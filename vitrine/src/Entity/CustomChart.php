<?php

namespace App\Entity;

use App\Repository\CustomChartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomChartRepository::class)]
class CustomChart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $colors = [];

    #[ORM\Column]
    private ?int $buttonType = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $fonts = [];

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function getButtonType(): ?int
    {
        return $this->buttonType;
    }

    public function setButtonType(int $buttonType): static
    {
        $this->buttonType = $buttonType;

        return $this;
    }

    public function getFonts(): array
    {
        return $this->fonts;
    }

    public function setFonts(array $fonts): static
    {
        $this->fonts = $fonts;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
