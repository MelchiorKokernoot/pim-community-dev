<?php

namespace Pim\Bundle\EnrichBundle\tests\integration\PQB\Sorter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Be aware that product are sorted by their ID (meaning their order of creation).
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InGroupSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    const IN_GROUP = 'in_group';

    public function testSortDescendant()
    {
        $groupId = $this->getGroupId('groupC');
        $key = sprintf('%s_%s', self::IN_GROUP, $groupId);

        $result = $this->executeSorter([[$key, Directions::DESCENDING]]);
        $this->assertOrder($result, ['baz', 'foo', 'bar', 'empty']);
    }

    public function testSortAscendant()
    {
        $groupId = $this->getGroupId('groupC');
        $key = sprintf('%s_%s', self::IN_GROUP, $groupId);

        $result = $this->executeSorter([[$key, Directions::ASCENDING]]);
        $this->assertOrder($result, ['foo', 'bar', 'empty', 'baz']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $groupId = $this->getGroupId('groupC');
        $key = sprintf('%s_%s', self::IN_GROUP, $groupId);

        $this->executeSorter([[$key, 'A_BAD_DIRECTION']]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $group = $this->get('pim_catalog.factory.group')->create();
        $this->get('pim_catalog.updater.group')->update(
            $group,
            [
                'code' => 'groupC',
                'type' => 'RELATED',
            ]
        );
        $this->get('pim_catalog.saver.group')->save($group);

        $this->createProduct('foo', ['groups' => ['groupA', 'groupB']]);
        $this->createProduct('bar', ['groups' => ['groupB']]);
        $this->createProduct('baz', ['groups' => ['groupC']]);
        $this->createProduct('empty', []);
    }

    /**
     * @param string $groupCode
     *
     * @return int
     */
    private function getGroupId(string $groupCode): int
    {
        $group = $this->get('pim_catalog.repository.group')->findOneByIdentifier($groupCode);

        if (null === $group) {
            throw new \LogicException(sprintf('Cannot find a group with code "%s"', $groupCode));
        }

        return $group->getId();
    }

}
