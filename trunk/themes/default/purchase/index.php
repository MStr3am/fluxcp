<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2>Purchase</h2>
<p>Items in this shop are purchased using <span class="keyword">donation credits</span> and not real money.  Donation Credits are rewarded to players who <a href="<?php echo $this->url('donate') ?>">make a donation to our server</a>, helping us cover the costs of maintaining and running the server.</p>
<h2><span class="shop-server-name"><?php echo htmlspecialchars($server->serverName) ?></span> Item Shop</h2>
<?php if ($items): ?>
<?php 
$evens = array();
$odds  = array();
foreach ($items as $i => $item) {
	if (!($i % 2)) {
		$evens[] = $item;
	}
	else {
		$odds[] = $item;
	}
}
?>
	
<table>
	<tr>
		<td width="50%">
			<?php foreach ($evens as $i => $item): ?>
				<div class="shop-item <?php echo (!($i % 2) ? 'even' : 'odd') ?>">
					<h4 class="shop-item-name"><?php echo htmlspecialchars($item->shop_item_name) ?></h4>
					<?php if ($item->shop_item_qty): ?>
					<p class="shop-item-qty">Quantity: <span class="qty"><?php echo number_format($item->shop_item_qty) ?></span></p>
					<?php endif ?>
					<p class="shop-item-cost"><span class="cost"><?php echo number_format($item->shop_item_cost) ?></span> credits</p>
					<p class="shop-item-info"><?php echo htmlspecialchars($item->shop_item_info) ?></p>
					<p class="shop-item-action">
						<a href="<?php echo $this->url('purchase', 'buy', array('id' => $item->shop_item_id)) ?>"><strong>Buy</strong></a>
						/ <a href="<?php echo $this->url('purchase', 'add', array('id' => $item->shop_item_id)) ?>">Add to Cart</a>
						/ <?php echo $this->linkToItem($item->shop_item_nameid, 'View Item') ?>
						<?php if ($auth->allowedToEditShopItem): ?>
						/ <a href="<?php echo $this->url('itemshop', 'edit', array('id' => $item->shop_item_id)) ?>">Modify</a>
						<?php endif ?>
						<?php if ($auth->allowedToDeleteShopItem): ?>
						/ <a href="<?php echo $this->url('itemshop', 'delete', array('id' => $item->shop_item_id)) ?>"
							onclick="return confirm('Are you sure you want to remove this item from the item shop?')">Delete</a>
						<?php endif ?>
					</p>
				</div>
			<?php endforeach ?>
		</td>
		
		<td width="50%">
			<?php foreach ($odds as $i => $item): ?>
				<div class="shop-item <?php echo (!($i % 2) ? 'even' : 'odd') ?>">
					<h4 class="shop-item-name"><?php echo htmlspecialchars($item->shop_item_name) ?></h4>
					<?php if ($item->shop_item_qty): ?>
					<p class="shop-item-qty">Quantity: <span class="qty"><?php echo number_format($item->shop_item_qty) ?></span></p>
					<?php endif ?>
					<p class="shop-item-cost"><span class="cost"><?php echo number_format($item->shop_item_cost) ?></span> credits</p>
					<p class="shop-item-info"><?php echo htmlspecialchars($item->shop_item_info) ?></p>
					<p class="shop-item-action">
						<a href="<?php echo $this->url('purchase', 'buy', array('id' => $item->shop_item_id)) ?>"><strong>Buy</strong></a>
						/ <a href="<?php echo $this->url('purchase', 'add', array('id' => $item->shop_item_id)) ?>">Add to Cart</a>
						/ <?php echo $this->linkToItem($item->shop_item_nameid, 'View Item') ?>
						<?php if ($auth->allowedToEditShopItem): ?>
						/ <a href="<?php echo $this->url('itemshop', 'edit', array('id' => $item->shop_item_id)) ?>">Modify</a>
						<?php endif ?>
						<?php if ($auth->allowedToDeleteShopItem): ?>
						/ <a href="<?php echo $this->url('itemshop', 'delete', array('id' => $item->shop_item_id)) ?>"
							onclick="return confirm('Are you sure you want to remove this item from the item shop?')">Delete</a>
						<?php endif ?>
					</p>
				</div>
			<?php endforeach ?>
		</td>
	</tr>
</table>
<?php else: ?>
<p>There are currently no items for sale.</p>
<?php endif ?>