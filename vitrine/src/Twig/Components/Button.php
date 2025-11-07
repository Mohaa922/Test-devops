<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Button
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $ButtonId = 0;

    #[LiveProp(writable: true)]
    public int $theme = 1;

    private array $baseStyles = [
        0 => 'rounded-b-full rounded-tr-full',
        1 => 'rounded-full',
        2 => 'rounded-sm',
    ];
    private array $themes = [
        1 => [
            'default' => 'btn_base bg-blue-600 text-white hover:bg-blue-700',
            'hover' => null,
            'animate' => 0
        ],
        2 => [
            'default' => 'btn_base bg-blue-600 text-white hover:bg-blue-700 hover:scale-105',
            'hover' => null,
            'animate' => 0
        ],
        3 => [
            'default' => 'btn_base btn_b_blue hover:bg-blue-700 hover:text-white',
            'hover' => null,
            'animate' => 0
        ],
        4 => [
            'default' => 'btn_base z-1 btn_b_blue relative hover:translate-x-1 hover:translate-y-1',
            'hover' => 'absolute top-1 left-1 bg-blue-600 w-full h-full',
            'animate' => 1,
        ],
        5 => [
            'default' => 'btn_base btn_b_blue z-1 relative hover:translate-x-1 hover:translate-y-1',
            'hover' => 'absolute inset-0 bg-blue-600 w-full h-full',
            'animate' => 1,
        ],
        6 => [
            'default' => 'btn_base btn_b_blue w-full relative overflow-hidden hover:text-white group z-1',
            'hover' => 'bg-blue-600 absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-500 z-[-1]',
            'animate' => 2,
        ],
    ];

    public function getButtonClassesbyId(): array
    {
        $result = [];
        $baseStyle = $this->baseStyles[$this->ButtonId] ?? $this->baseStyles[0];

        foreach ($this->themes as $themeId => $theme) {
            $result[$themeId] = [
                'default' => $theme['default'] . ' ' . $baseStyle,
                'hover' => $theme['hover'] ? $theme['hover'] . ' ' . $baseStyle : null,
                'animate' => $theme['animate']
            ];
        }

        return $result;
    }

    public function getThemeStyles(): array
    {
        $theme = $this->themes[$this->theme] ?? $this->themes[1];
        $baseStyle = $this->baseStyles[$this->ButtonId] ?? $this->baseStyles[0];

        return [
            'default' => $theme['default'] . ' ' . $baseStyle,
            'hover' => $theme['hover'] ? $theme['hover'] . ' ' . $baseStyle : null,
            'animate' => $theme['animate']
        ];
    }
}
