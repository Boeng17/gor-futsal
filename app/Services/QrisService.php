<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class QrisService
{
    private const CONFIG_PATH = 'qris_config.json';

    public function getConfig(): array
    {
        $defaultStatic = '00020101021126610014COM.GO-JEK.WWW01189360091431234567890210G1234567890303UMI51440014ID.CO.QRIS.WWW0215ID10200123456780303UMI5204599953033605802ID5919GOR FUTSAL NUSANTAR6009TANGERANG61051541763049EC9';

        if (Storage::exists(self::CONFIG_PATH)) {
            $decoded = json_decode(Storage::get(self::CONFIG_PATH), true);
            if (is_array($decoded) && ! empty($decoded['raw_qris'])) {
                return [
                    'raw_qris'      => $decoded['raw_qris'],
                    'merchant_name' => $decoded['merchant_name'] ?? 'GOR FUTSAL NUSANTARA',
                    'merchant_city' => $decoded['merchant_city'] ?? 'TANGERANG SELATAN',
                    'is_custom'     => true,
                    'updated_at'    => $decoded['updated_at'] ?? null,
                ];
            }
        }

        return [
            'raw_qris'      => $defaultStatic,
            'merchant_name' => 'GOR FUTSAL NUSANTARA',
            'merchant_city' => 'TANGERANG SELATAN',
            'is_custom'     => false,
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

        $extractedName = $merchantName ?: ($tagMap['59'] ?? 'GOR FUTSAL NUSANTARA');
        $extractedCity = $merchantCity ?: ($tagMap['60'] ?? 'TANGERANG SELATAN');

        $payload = [
            'raw_qris'      => $cleaned,
            'merchant_name' => strtoupper(trim($extractedName)),
            'merchant_city' => strtoupper(trim($extractedCity)),
            'updated_at'    => now()->toDateTimeString(),
        ];

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
