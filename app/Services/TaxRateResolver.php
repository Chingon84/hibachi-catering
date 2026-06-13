<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\CustomTaxRate;

class TaxRateResolver
{
    public function defaultRate(): float
    {
        $profile = AdminSetting::valuesForGroup('business_profile', [
            'default_tax_rate' => '10.25',
        ]);

        return $this->normalizeRate($profile['default_tax_rate'] ?? '10.25');
    }

    public function rateForCity(?string $cityName): float
    {
        $cityKey = CustomTaxRate::cityKey((string) $cityName);
        if ($cityKey !== '') {
            $customRate = CustomTaxRate::query()
                ->where('city_key', $cityKey)
                ->where('is_active', true)
                ->value('tax_rate');

            if ($customRate !== null) {
                return $this->normalizeRate($customRate);
            }
        }

        return $this->defaultRate();
    }

    public function rateForInvoiceData(array $data): float
    {
        return $this->rateForCity($this->invoiceCity($data));
    }

    public function invoiceCity(array $data): ?string
    {
        $explicitCity = trim((string) ($data['customer_city'] ?? ''));
        if ($explicitCity !== '') {
            return $explicitCity;
        }

        return $this->cityFromAddress((string) ($data['customer_address'] ?? ''));
    }

    public function invoiceCityForStorage(array $data): ?string
    {
        $cityName = $this->invoiceCity($data);
        $cityKey = CustomTaxRate::cityKey((string) $cityName);

        if ($cityKey !== '') {
            $customCity = CustomTaxRate::query()
                ->where('city_key', $cityKey)
                ->where('is_active', true)
                ->value('city_name');

            if ($customCity !== null) {
                return $customCity;
            }
        }

        return $cityName;
    }

    public function frontendRates(): array
    {
        return CustomTaxRate::query()
            ->where('is_active', true)
            ->orderBy('city_name')
            ->get(['city_name', 'city_key', 'tax_rate'])
            ->map(fn (CustomTaxRate $rate) => [
                'city_name' => $rate->city_name,
                'city_key' => $rate->city_key,
                'tax_rate' => number_format((float) $rate->tax_rate, 2, '.', ''),
            ])
            ->values()
            ->all();
    }

    private function cityFromAddress(string $address): ?string
    {
        $parts = collect(explode(',', $address))
            ->map(fn (string $part) => trim($part))
            ->filter()
            ->values();

        if ($parts->count() < 2) {
            return null;
        }

        $last = (string) $parts->last();
        if (preg_match('/^\d{5}(?:-\d{4})?$/', $last)) {
            return $parts->count() >= 3 ? (string) $parts->get($parts->count() - 2) : null;
        }

        if (preg_match('/^[A-Z]{2}\s+\d{5}(?:-\d{4})?$/i', $last)) {
            return $parts->count() >= 2 ? (string) $parts->get($parts->count() - 2) : null;
        }

        return (string) $parts->get(1);
    }

    private function normalizeRate(mixed $value): float
    {
        return round(min(100, max(0, (float) $value)), 2);
    }
}
