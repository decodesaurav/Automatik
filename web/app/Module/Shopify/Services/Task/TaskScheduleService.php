<?php

namespace App\Module\Shopify\Services\Task;
use App\Models\ShopifyProduct;
use App\Models\ShopifyProductVariant;
use App\Models\TaskAdjustment;
use App\Models\TaskCondition;
use App\Module\Shopify\Repositories\Task\TaskScheduleRepository;



class TaskScheduleService
{
	protected TaskScheduleRepository $taskScheduleRepository;

	public function __construct(TaskScheduleRepository $taskScheduleRepository)
	{
		$this->taskScheduleRepository = $taskScheduleRepository;
	}


	public function setJob($task, $onQueue = 'task_scheduler')
	{
		$this->processTask($task);
	}

	public function processTask($task)
	{
		$products = $this->getProductAfterConditionApply($task);
		$applyAdjustmentToProduct = $this->applyAdjustmentToProduct($products, $task);
	}

	public function getProductAfterConditionApply($task)
	{
		$filters = [];
		$conditions = TaskCondition::where('task_id', $task->id)->get();
		// if(count($conditions))
		$filters = self::formatConditions($conditions);
		$query = ShopifyProduct::query();
		$query->whereHas('variations', function ($variantQuery) use ($filters) {
			foreach ($filters as $filter) {
				if (in_array($filter[0], ['price', 'stock'])) {
					$variantQuery->where($filter[0], $filter[1], $filter[2]);
				}
			}
		});
		foreach ($filters as $filter) {
			if (in_array($filter[0], ['title', 'collection_id'])) {
				$query->where($filter[0], $filter[1], $filter[2]);
			}
		}

		$products = $query->get();
		return $products;
	}

	public function applyAdjustmentToProduct($products, $task)
	{
		$adjustments = TaskAdjustment::where('task_id', $task->id)->first();
		if ($adjustments) {
			foreach ($products as $product) {
				// Prepare the data for updating Shopify
				$updateData = $this->prepareAdjustmentData($product, $adjustments);

				// Call Shopify API to update the product
				$this->updateProductOnShopify($product, $updateData);
			}
		}
	}
	public function prepareAdjustmentData($product, $adjustment)
	{
		$data = [];

		foreach ($product->variations as $variation) {
			// Adjust price or inventory (stock)
			if ($adjustment->field === 'price') {
				$data[] = $this->preparePriceAdjustmentData($variation, $adjustment);
			} elseif ($adjustment->field === 'inventory') {
				$data[] = $this->prepareInventoryAdjustmentData($variation, $adjustment);
			}
		}

		return $data;
	}
	public function preparePriceAdjustmentData($variation, $adjustment)
	{
		$currentPrice = $variation->price;
		$newPrice = $currentPrice;

		if ($adjustment->adjustment_type === 'percentage') {
			$adjustmentValue = ($currentPrice * $adjustment->value) / 100;
		} else {
			$adjustmentValue = $adjustment->value;
		}

		if ($adjustment->method === 'increase') {
			$newPrice = $currentPrice + $adjustmentValue;
		} elseif ($adjustment->method === 'decrease') {
			$newPrice = $currentPrice - $adjustmentValue;
		}

		return [
			'id' => $variation->shopify_variant_id, // Assuming this is the Shopify variant ID
			'price' => $newPrice,
		];
	}
	public function prepareInventoryAdjustmentData($variation, $adjustment)
	{
		$currentStock = $variation->stock;
		$newStock = $currentStock;

		if ($adjustment->method === 'increase') {
			$newStock = $currentStock + $adjustment->value;
		} elseif ($adjustment->method === 'decrease') {
			$newStock = max(0, $currentStock - $adjustment->value); // Ensure stock doesn't go below 0
		}

		return [
			'id' => $variation->shopify_variant_id, // Assuming this is the Shopify variant ID
			'inventory_quantity' => $newStock,
		];
	}
	public function formatConditions($conditions)
	{
		$filters = [];

		foreach ($conditions as $condition) {
			$operator = $this->mapOperator($condition->operator);
			if ($operator) {
				$filters[] = [
					$condition->condition_field,
					$operator,
					$condition->value
				];
			}
		}

		return $filters;
	}

	private function mapOperator($operator)
	{
		$operatorMap = [
			'greater_than' => '>',
			'less_than' => '<',
			'equals' => '=',
			'not_equals' => '!=',
			'like' => 'like',
			'in' => 'in',
		];

		return $operatorMap[$operator] ?? null;
	}

}