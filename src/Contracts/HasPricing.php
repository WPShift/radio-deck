<?php

namespace JaOcero\RadioDeck\Contracts;

interface HasPricing
{
    public function getPricing(): ?string;
}
