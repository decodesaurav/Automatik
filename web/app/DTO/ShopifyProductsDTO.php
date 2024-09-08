<?php
namespace App\DTO;
class ShopifyProductsDTO
{
	public $id;
	public $title;

	public $description;
	public $vendor;

	public $product_type;
	public $created_at;
	public $updated_at;
	public $published_at;
	public $published_scope;
	public $tags;
	public $status;
	public $options;
	public $images;

	public function __construct($shopifyProduct)
	{
		$this->id = $shopifyProduct->id;
		$this->title = $shopifyProduct->title;
		$this->description = $shopifyProduct->body_html;
		$this->vendor = $shopifyProduct->vendor;
		$this->product_type = $shopifyProduct->product_type;
		$this->created_at = $shopifyProduct->created_at;
		$this->updated_at = $shopifyProduct->updated_at;
		$this->published_at = $shopifyProduct->updated_at;
		$this->published_scope = $shopifyProduct->published_scope;
		$this->tags = $shopifyProduct->tags;
		$this->status = $shopifyProduct->status;
		$this->options = json_encode($shopifyProduct->options);
		$this->images = json_encode($shopifyProduct->images);
	}

	public function toModelAttributes()
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'description' => $this->description,
			'vendor' => $this->vendor,
			'product_type' => $this->product_type,
			'created_at' => $this->created_at,
			'updated_at' => $this->updated_at,
			'published_at' => $this->published_at,
			'published_scope' => $this->published_scope,
			'tags' => $this->tags,
			'status' => $this->status,
			'options' => $this->options,
			'images' => $this->images

		];
	}

}