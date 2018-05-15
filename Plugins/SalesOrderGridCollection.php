<?php 
/**
  * @author     DCKAP <extensions@dckap.com>
  * @package    DCKAP_Orderexport
  * @copyright  Copyright (c) 2017 DCKAP Inc (http://www.dckap.com)
  * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
namespace DCKAP\Orderexport\Plugins;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollections;

class SalesOrderGridCollection
{
    private $messageManager;
    private $collection;
    private $registry;

    public function __construct(MessageManager $messageManager,
        SalesOrderGridCollections $collection,
        Registry $registry
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->registry = $registry;
    }

    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName    
    ) {        
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof $this->collection
            ) { 
                if (is_null($this->registry->registry('shipment_joined'))) {
                    $select = $this->collection->getSelect();
                    $select->join(
                        ["sst" => "sales_shipment_track"],
                        'main_table.entity_id = sst.entity_id',
                        'sst.track_number'
                    )
                        ->distinct();
                    $this->registry->register('shipment_joined', true);
                }                                  
            }

        }
        return $this->collection;
    }
}