<?php

namespace JaOcero\RadioDeck\Forms\Components;

use Closure;
use Filament\Support\Concerns\HasAlignment;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;
use JaOcero\RadioDeck\Contracts\HasDescriptions;
use JaOcero\RadioDeck\Contracts\HasIcons;
use JaOcero\RadioDeck\Contracts\HasPricing;
use JaOcero\RadioDeck\Contracts\HasTrial;
use JaOcero\RadioDeck\Intermediary\IntermediaryRadio;
use JaOcero\RadioDeck\Traits\HasDirection;
use JaOcero\RadioDeck\Traits\HasExtraCardsAttributes;
use JaOcero\RadioDeck\Traits\HasExtraDescriptionsAttributes;
use JaOcero\RadioDeck\Traits\HasExtraOptionsAttributes;
use JaOcero\RadioDeck\Traits\HasGap;
use JaOcero\RadioDeck\Traits\HasIconSizes;
use JaOcero\RadioDeck\Traits\HasPadding;

class RadioDeck extends IntermediaryRadio
{
    use HasAlignment;
    use HasColor;
    use HasDirection;
    use HasExtraCardsAttributes;
    use HasExtraDescriptionsAttributes;
    use HasExtraOptionsAttributes;
    use HasGap;
    use HasIcon;
    use HasIconSizes;
    use HasPadding;

    protected array|Arrayable|Closure|string|null $icons = null;

    protected array|Arrayable|Closure|string $descriptions = [];

    protected array|Arrayable|Closure|string|null $pricing = null;

    protected array|Arrayable|Closure|string|null $trial = null;

    protected bool|Closure $isMultiple = false;

    protected string $view = 'radio-deck::forms.components.radio-deck';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(fn (RadioDeck $component): mixed => $component->isMultiple() ? [] : null);

        $this->afterStateHydrated(static function (RadioDeck $component, $state): void {
            if (! $component->isMultiple()) {
                return;
            }

            if (is_array($state)) {
                return;
            }

            $component->state([]);
        });
    }

    public function icons(array|Arrayable|string|Closure|null $icons): static
    {
        $this->icons = $icons;

        return $this;
    }

    public function descriptions(array|Arrayable|string|Closure $descriptions): static
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    public function pricing(array|Arrayable|string|Closure|null $pricing): static
    {
        $this->pricing = $pricing;

        return $this;
    }

    public function trial(array|Arrayable|string|Closure|null $trial): static
    {
        $this->trial = $trial;

        return $this;
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function hasIcons($value): bool
    {
        return $this->hasKeyInArray($value, $this->getIcons());
    }

    public function getIcons(): mixed
    {
        return $this->evaluateEnumOrArray($this->icons, HasIcons::class);
    }

    public function getIcon($value): ?string
    {
        return $this->getIcons()[$value] ?? null;
    }

    public function getDescriptions(): array
    {
        return $this->evaluateEnumOrArray($this->descriptions, HasDescriptions::class);
    }

    public function hasPricing($value): bool
    {
        $pricing = $this->getPricing();

        return is_array($pricing) && array_key_exists($value, $pricing);
    }

    public function getPricing($value): mixed
    {
        $pricing = $this->evaluate($this->pricing);

        if (is_array($pricing) && array_key_exists($value, $pricing)) {
            return $pricing[$value];
        }

        return null;
    }


    public function hasTrial($value): bool
    {
        $trial = $this->getTrial();

        return is_array($trial) && array_key_exists($value, $trial);
    }

    public function getTrial($value): mixed
    {
        $trial = $this->evaluate($this->trial);

        if (is_array($trial) && array_key_exists($value, $trial)) {
            return $trial[$value];
        }

        return null;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    /**
     * Helper method to check if a key exists in the evaluated array
     */
    private function hasKeyInArray($value, $array): bool
    {
        if ($value !== null && ! empty($array)) {
            return array_key_exists($value, $array);
        }

        return false;
    }

    /**
     * Helper method to evaluate an enum or array and return the processed result.
     */
    private function evaluateEnumOrArray($input, string $enumClass): mixed
    {
        $evaluated = $this->evaluate($input);

        if (is_string($evaluated) && enum_exists($evaluated)) {
            if (is_a($evaluated, $enumClass, allow_string: true)) {
                return collect($evaluated::cases())
                    ->mapWithKeys(fn ($case) => [
                        ($case?->value ?? $case->name) => $case->name,
                    ])
                    ->all();
            }

            return collect($evaluated::cases())
                ->mapWithKeys(fn ($case) => [
                    ($case?->value ?? $case->name) => $case->name,
                ])
                ->all();
        }

        if ($evaluated instanceof Arrayable) {
            $evaluated = $evaluated->toArray();
        }

        return $evaluated;
    }
}
