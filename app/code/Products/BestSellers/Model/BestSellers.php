<?php

namespace Products\BestSellers\Model;

use Products\BestSellers\Api\BestSellerInterface;
use \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as BestSellersCollection;

class BestSellers implements BestSellerInterface
{
    /**
     * Keys to be sorted by
     * @var array KEYS
     */
    const PRODUCT_KEYS = ['qty_ordered', 'product_price'];

    /**
     * @var int DEFAULT_PRODUCT_COUNT
     */
    const DEFAULT_PRODUCT_COUNT = 2;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var $_productCollection
     */
    protected $_productCollection;

    /**
     * @var $_collection
     */
    protected $_collection;

    /**
     * @var $bestSellerCollection BestSellersCollection
     */
    protected $_bestSellerCollection;

    /**
     * BestSellers constructor.
     */
    public function __construct()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->_productCollection = $this->_objectManager->create(
            'Magento\Reports\Model\ResourceModel\Report\Collection\Factory'
        );
    }

    /**
     * Returns Best Seller Collection
     *
     * @api
     * @param int $pageSize
     * @return mixed $result Returns Best Seller Collection
     */
    public function get($pageSize = self::DEFAULT_PRODUCT_COUNT): array
    {
        $request = $this->getParameters();

        $fromDate = $request['fromDate'];
        $toDate = $request['toDate'];
        $offset = $request['offset'];
        $sort = $request['sort'];

        // Collection should be loaded when route is called, not during Dependency Injection
        // Load the BestSellers Collection with Date Range filter
        $collection = $this->_productCollection
            ->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection')
            ->setOrder($sort)
            ->addFieldToFilter('period', ['from' => $fromDate, 'to' => $toDate, 'date' => true])
            ->setPageSize($pageSize)
            ->setCurPage($offset)
            ->load();

        // Insert Item data into it's own array for response result
        $bestSellerCollection = [];
        foreach ($collection as $item) {
            $bestSellerCollection[] = $item->getData();
        }

        // Returns based on the header e.g. 'Accept' => 'application/json'
        return [
            'data' => [
                'data'   => $bestSellerCollection,
                'items'  => $pageSize,
                'offset' => $offset,
            ],
        ];
    }

    /**
     * Return $_GET params for readability
     * @return array
     */
    private function getParameters(): array
    {
        $sort = (in_array($_GET['sort'], self::PRODUCT_KEYS)) ? $_GET['sort'] : self::PRODUCT_KEYS[0];

        return [
            'sort'     => $sort,
            'offset'   => (int) $_GET['offset'],
            'toDate'   => $_GET['toDate'],
            'fromDate' => $_GET['fromDate'],
        ];
    }
}