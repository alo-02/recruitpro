<?php

namespace App\Enums;

enum JobCategory: string
{
    case Technology = 'technology';
    case Marketing = 'marketing';
    case Finance = 'finance';
    case Engineering = 'engineering';
    case Design = 'design';
    case Healthcare = 'healthcare';
    case Education = 'education';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Technology => 'Technology',
            self::Marketing => 'Marketing',
            self::Finance => 'Finance',
            self::Engineering => 'Engineering',
            self::Design => 'Design',
            self::Healthcare => 'Healthcare',
            self::Education => 'Education',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Technology => 'terminal',
            self::Marketing => 'campaign',
            self::Finance => 'account_balance',
            self::Engineering => 'architecture',
            self::Design => 'palette',
            self::Healthcare => 'health_and_safety',
            self::Education => 'school',
            self::Other => 'work',
        };
    }

    /**
     * @return list<array{value:string,label:string,icon:string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => [
                'value' => $c->value,
                'label' => $c->label(),
                'icon' => $c->icon(),
            ],
            self::cases()
        );
    }
}
