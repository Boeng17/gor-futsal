<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class QrisService
{
    private const CONFIG_PATH = 'qris_config.json';
    private const TABLE_NAME = 'qris_configs';

    private function ensureTable(): bool
    {
        try {
            if (! Schema::hasTable(self::TABLE_NAME)) {
                Schema::create(self::TABLE_NAME, function (Blueprint $table) {
                    $table->id();
                    $table->text('raw_qris');
                    $table->string('merchant_name', 100);
                    $table->string('merchant_city', 100);
                    $table->timestamps();
                });
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getConfig(): array
    {
        $defaultStatic = '00020101021126610014COM.GO-JEK.WWW01189360091430693731450210G0693731450303UMI51440014ID.CO.QRIS.WWW0215ID10266223997430303UMI5204899953033605802ID5918AFIFAH KHOIRUNNISA6005BOGOR61051692162070703A0163045DCD';

        if ($this->ensureTable()) {
            try {
                $row = DB::table(self::TABLE_NAME)->orderByDesc('id')->first();
                if ($row && ! empty($row->raw_qris)) {
                    return [
                        'raw_qris'      => $row->raw_qris,
                        'merchant_name' => $row->merchant_name ?: 'AFIFAH KHOIRUNNISA',
                        'merchant_city' => $row->merchant_city ?: 'BOGOR',
                        'is_custom'     => true,
                        'updated_at'    => $row->updated_at,
                    ];
                }
            } catch (\Throwable $e) {
            }
        }

        if (Storage::exists(self::CONFIG_PATH)) {
            $decoded = json_decode(Storage::get(self::CONFIG_PATH), true);
            if (is_array($decoded) && ! empty($decoded['raw_qris'])) {
                return [
                    'raw_qris'      => $decoded['raw_qris'],
                    'merchant_name' => $decoded['merchant_name'] ?? 'AFIFAH KHOIRUNNISA',
                    'merchant_city' => $decoded['merchant_city'] ?? 'BOGOR',
                    'is_custom'     => true,
                    'updated_at'    => $decoded['updated_at'] ?? null,
                ];
            }
        }

        return [
            'raw_qris'      => $defaultStatic,
            'merchant_name' => 'AFIFAH KHOIRUNNISA',
            'merchant_city' => 'BOGOR',
            'is_custom'     => true,
            'updated_at'    => null,
        ];
    }

    public function saveMerchantConfig(string $rawQris, ?string $merchantName = null, ?string $merchantCity = null): array
    {
        $cleaned = trim(str_replace(["\r", "\n"], '', $rawQris));
        [$tlvList, $isComplete] = $this->parseOrderedTlv($cleaned);
        $tagMap = $this->tlvListToMap($tlvList);

        if (empty($tlvList) || ! $isComplete || ! isset($tagMap['00']) || ! isset($tagMap['58'])) {
            throw new \InvalidArgumentException('Format payload QRIS EMVCo tidak valid. Pastikan string dimulai dengan 000201, memiliki tag 5802ID, dan panjang karakter setiap tag utuh tanpa terpotong.');
        }

        if (isset($tagMap['63']) && strlen($cleaned) >= 8) {
            $withoutCrc = substr($cleaned, 0, -4);
            $expectedCrc = $this->crc16CcittFalse($withoutCrc);
            $actualCrc = strtoupper($tagMap['63']);
            if ($actualCrc !== $expectedCrc) {
                throw new \InvalidArgumentException("Checksum CRC-16 pada string QRIS tidak cocok (terbaca {$actualCrc}, seharusnya {$expectedCrc}). Pastikan string QRIS disalin utuh beserta spasi nama merchant.");
            }
        }

        $extractedName = strtoupper(trim($merchantName ?: ($tagMap['59'] ?? 'AFIFAH KHOIRUNNISA')));
        $extractedCity = strtoupper(trim($merchantCity ?: ($tagMap['60'] ?? 'BOGOR')));
        $now = now()->toDateTimeString();

        $payload = [
            'raw_qris'      => $cleaned,
            'merchant_name' => $extractedName,
            'merchant_city' => $extractedCity,
            'updated_at'    => $now,
        ];

        if ($this->ensureTable()) {
            $existing = DB::table(self::TABLE_NAME)->orderByDesc('id')->first();
            if ($existing) {
                DB::table(self::TABLE_NAME)->where('id', $existing->id)->update([
                    'raw_qris'      => $cleaned,
                    'merchant_name' => $extractedName,
                    'merchant_city' => $extractedCity,
                    'updated_at'    => $now,
                ]);
            } else {
                DB::table(self::TABLE_NAME)->insert([
                    'raw_qris'      => $cleaned,
                    'merchant_name' => $extractedName,
                    'merchant_city' => $extractedCity,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        Storage::put(self::CONFIG_PATH, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $payload;
    }

    public function generateDynamicPayload(int $amount, ?string $bookingCode = null): string
    {
        $config = $this->getConfig();
        $raw = trim(str_replace(["\r", "\n"], '', $config['raw_qris']));
        [$tlvList, $isComplete] = $this->parseOrderedTlv($raw);

        if (empty($tlvList) || ! $isComplete) {
            return $raw;
        }

        $amountStr = (string) max(1, (int) round($amount));
        $rebuilt = [];
        $insertedAmount = false;

        foreach ($tlvList as [$tag, $val]) {
            if ($tag === '63') {
                continue;
            }

            if ($tag === '01') {
                $rebuilt[] = ['01', '12'];
                continue;
            }

            if ($tag === '54') {
                $rebuilt[] = ['54', $amountStr];
                $insertedAmount = true;
                continue;
            }

            if ($tag === '58') {
                if (! $insertedAmount) {
                    $rebuilt[] = ['54', $amountStr];
                    $insertedAmount = true;
                }
                $rebuilt[] = [$tag, $val];
                continue;
            }

            $rebuilt[] = [$tag, $val];
        }

        if (! $insertedAmount) {
            $rebuilt[] = ['54', $amountStr];
        }

        $body = '';
        foreach ($rebuilt as [$tag, $val]) {
            $len = str_pad((string) strlen($val), 2, '0', STR_PAD_LEFT);
            $body .= $tag . $len . $val;
        }

        $bodyWithCrcPrefix = $body . '6304';
        $crc = $this->crc16CcittFalse($bodyWithCrcPrefix);

        return $bodyWithCrcPrefix . $crc;
    }

    private function parseOrderedTlv(string $payload): array
    {
        $list = [];
        $pos = 0;
        $totalLen = strlen($payload);

        while ($pos + 4 <= $totalLen) {
            $tag = substr($payload, $pos, 2);
            $lenStr = substr($payload, $pos + 2, 2);

            if (! ctype_digit($lenStr)) {
                break;
            }

            $len = (int) $lenStr;
            if ($pos + 4 + $len > $totalLen) {
                break;
            }

            $val = substr($payload, $pos + 4, $len);
            $list[] = [$tag, $val];
            $pos += 4 + $len;

            if ($tag === '63') {
                break;
            }
        }

        return [$list, $pos === $totalLen && $totalLen > 0];
    }

    private function tlvListToMap(array $tlvList): array
    {
        $map = [];
        foreach ($tlvList as [$tag, $val]) {
            $map[$tag] = $val;
        }
        return $map;
    }

    public function crc16CcittFalse(string $data): string
    {
        $crc = 0xFFFF;
        $bytes = unpack('C*', $data);

        foreach ($bytes as $byte) {
            $crc ^= ($byte << 8);
            for ($i = 0; $i < 8; $i++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
