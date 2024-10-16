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

    protected array|Arrayable|Closure|string $pricing = [];

    protected array|Arrayable|Closure|string|null $colors = null;

    protected array|Closure $disabledOptions = [];

    protected string|Closure|null $disabledReason = null;

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

    public function pricing(array|Arrayable|string|Closure $pricing): static
    {
        $this->pricing = $pricing;

        return $this;
    }

    public function colors(array|Arrayable|string|Closure|null $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function disabledOptions(array|Closure $options): static
    {
        $this->disabledOptions = $options;

        return $this;
    }

    public function disabledReason(string|Closure|null $reason): static
    {
        $this->disabledReason = $reason;

        return $this;
    }

    public function getDisabledOptions(): array
    {
        $options = $this->evaluate($this->disabledOptions);

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        return $options ?? [];
    }

    public function getDisabledReason(): ?string
    {
        return $this->evaluate($this->disabledReason);
    }

    public function isOptionDisabled($value, string $label): bool
    {
        return in_array($value, $this->getDisabledOptions());
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    /**
     * Determine if an icon exists for the given value.
     *
     * @param  array-key  $value
     */
    public function hasIcons($value): bool
    {
        if ($value !== null && ! empty($this->getIcons())) {
            return array_key_exists($value, $this->getIcons());
        }

        return false;
    }

    /**
     * Get the icons array or evaluated result.
     *
     * @return array|Closure|null
     */
    public function getIcons(): mixed
    {
        $icons = $this->evaluate($this->icons);

        $enum = $icons;

        if (is_string($enum) && enum_exists($enum)) {
            if (is_a($enum, HasIcons::class, allow_string: true)) {
                return collect($enum::cases())
                    ->mapWithKeys(fn ($case) => [
                        ($case?->value ?? $case->name) => $case->getIcons() ?? $case->name,
                    ])
                    ->all();
            }

            return collect($enum::cases())
                ->mapWithKeys(fn ($case) => [
                    ($case?->value ?? $case->name) => $case->name,
                ])
                ->all();
        }

        if ($icons instanceof Arrayable) {
            $icons = $icons->toArray();
        }

        return $icons;
    }

    /**
     * Get the icon for the given value.
     *
     * @param  array-key  $value
     */
    public function getIcon($value): ?string
    {
        return $this->getIcons()[$value] ?? null;
    }

    /**
     * Get the descriptions array.
     *
     * @return array<string|Htmlable>
     */
    public function getDescriptions(): array
    {
        $descriptions = $this->evaluate($this->descriptions);

        $enum = $descriptions;

        if (is_string($enum) && enum_exists($enum)) {
            if (is_a($enum, HasDescriptions::class, allow_string: true)) {
                return collect($enum::cases())
                    ->mapWithKeys(fn ($case) => [
                        ($case?->value ?? $case->name) => $case->getDescriptions() ?? $case->name,
                    ])
                    ->all();
            }

            return collect($enum::cases())
                ->mapWithKeys(fn ($case) => [
                    ($case?->value ?? $case->name) => $case->name,
                ])
                ->all();
        }

        if ($descriptions instanceof Arrayable) {
            $descriptions = $descriptions->toArray();
        }

        return $descriptions;
    }

    /**
     * Determine if pricing exists for the given value.
     *
     * @param  array-key  $value
     */
    public function hasPricing($value): bool
    {
        return array_key_exists($value, $this->getPricings());
    }

    /**
     * Get the pricing for the given value.
     *
     * @param  array-key  $value
     */
    public function getPricing($value): ?string
    {
        return $this->getPricings()[$value] ?? null;
    }

    /**
     * Get the pricing array.
     *
     * @return array<string|Htmlable>
     */
    public function getPricings(): array
    {
        $pricing = $this->evaluate($this->pricing);

        if ($pricing instanceof Arrayable) {
            $pricing = $pricing->toArray();
        }

        return $pricing;
    }

    /**
     * Get the colors array.
     *
     * @return array<string>
     */
    public function getColors(): array
    {
        $colors = $this->evaluate($this->colors);

        if ($colors instanceof Arrayable) {
            $colors = $colors->toArray();
        }

        return $colors ?? [];
    }

    /**
     * Get the color for the given option value.
     *
     * @param  array-key  $value
     */
    public function getOptionColor($value): ?string
    {
        return $this->getColors()[$value] ?? $this->getColor();
    }

    /**
     * Check if the field allows multiple selections.
     */
    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }
}
