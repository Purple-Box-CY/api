<?php

namespace App\ApiDTO\Response\Settings;

use App\Entity\Settings\Country;
use App\Service\Utility\DomainHelper;

class ResponseCountriesDTO
{
    public function __construct(
        /** @var CountryDTO[] */
        public array $countries = [],
    ) {
    }

    public static function create(
        array $countries,
    ): self {
        $items = [];
        $domain = DomainHelper::cdnIsEnabled() ? DomainHelper::getCdnDomain() : DomainHelper::getApiProjectDomain();
        foreach ($countries as $code => $name) {
            if (in_array($code, Country::FLAGS_EXCLUDE_SHOW)) {
                continue;
            }

            $items[]=CountryDTO::create(
                $code,
                $name,
                $domain.'/'.(Country::FLAGS[$code] ?? Country::FLAGS[Country::DEFAULT_CODE]),
            );
        }

        return new self(
            countries: $items,
        );
    }
}
