<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\ReadModel;

/**
 * Represent data regarding the variant product completenesses to build the ratio on the PMEF.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantProductCompleteness
{
    /** @var array */
    private $completenesses;

    /**
     * @param array $completenesses
     */
    public function __construct(array $completenesses)
    {
        $this->completenesses = $completenesses;
    }

    /**
     * Return the number of variant product depending on the channel and the locale
     *
     * @return array
     */
    public function completenesses(): array
    {
        $completenesses = [];
        foreach ($this->completenesses as $completeness) {
            $locale = $completeness['lo'];
            $channel = $completeness['ch'];
            if (!isset($completenesses[$channel][$locale])) {
                $completenesses[$channel][$locale] = 0;
            }

            $completenesses[$channel][$locale] = $completenesses[$channel][$locale] + $completeness['co'];
        }

        return $completenesses;
    }

    /**
     * Return the total number of product variant
     *
     * @return int
     */
    public function total(): int
    {
        return count(
            array_unique(
                array_column($this->completenesses, 'co')
            )
        );
    }
}
