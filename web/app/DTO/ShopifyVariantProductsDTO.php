<?php
namespace App\DTO;
class ShopifyVariantProductsDTO
{
	public $id;
	public $title;

	public $sku;
	public $inventory_item_id;

	public $inventory_quantity;
	public $created_at;
	public $updated_at;
	public $published_at;
	public $image_id;
	public $old_inventory_quantity;
	public $requires_shipping;

	public $inventory_management;
	public $taxable;
	public $compare_at_price;
	public $inventory_policy;
	public $position;
	public $price;
	public $product_id;


	public function __construct($shopifyProduct, $product_id)
	{
		$this->id = $shopifyProduct->id;
		$this->product_id = $product_id;
		$this->title = $shopifyProduct->title;
		$this->price = $shopifyProduct->price;
		$this->position = $shopifyProduct->position;
		$this->inventory_policy = $shopifyProduct->inventory_policy;
		$this->compare_at_price = $shopifyProduct->compare_at_price;
		$this->created_at = $shopifyProduct->created_at;
		$this->updated_at = $shopifyProduct->updated_at;
		$this->published_at = $shopifyProduct->updated_at;
		$this->taxable = $shopifyProduct->published_scope;
		$this->inventory_management = $shopifyProduct->tags;
		$this->requires_shipping = $shopifyProduct->status;
		$this->sku = json_encode($shopifyProduct->options);
		$this->inventory_item_id = json_encode($shopifyProduct->images);
		$this->inventory_quantity = $shopifyProduct->inventory_quantity;
		$this->old_inventory_quantity = $shopifyProduct->old_inventory_quantity;
		$this->image_id = $shopifyProduct->image_id;
	}

	public function toModelAttributes()
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'product_id' => $this->product_id,
			'price' => $this->price,
			'position' => $this->position,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
			'published_at' => $this->published_at,
			'inventory_policy' => $this->inventory_policy,
			'compare_at_price' => $this->compare_at_price,
			'taxable' => $this->taxable,
			'inventory_management' => $this->inventory_management,
			'requires_shipping' => $this->requires_shipping,
			'sku' => $this->sku,
			'inventory_item_id' => $this->inventory_item_id,
			'inventory_quantity' => $this->inventory_quantity,
			'old_inventory_quantity' => $this->old_inventory_quantity,
			'image_id' => $this->image_id,
		];
	}

}