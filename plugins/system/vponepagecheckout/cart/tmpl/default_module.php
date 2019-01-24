<?php 
/*--------------------------------------------------------------------------------------------------------
# VP One Page Checkout - Joomla! System Plugin for VirtueMart 3
----------------------------------------------------------------------------------------------------------
# Copyright:     Copyright (C) 2012 - 2015 VirtuePlanet Services LLP. All Rights Reserved.
# License:       GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
# Author:        Abhishek Das
# Email:         info@virtueplanet.com
# Websites:      http://www.virtueplanet.com
----------------------------------------------------------------------------------------------------------
$Revision: 28 $
$LastChangedDate: 2015-01-25 21:03:38 +0530 (Sun, 25 Jan 2015) $
$Id: default_module.php 28 2015-01-25 15:33:38Z abhishekdas $
----------------------------------------------------------------------------------------------------------*/
defined ( '_JEXEC' ) or die ( 'Restricted access' );
$modules = $this->getCartModules();
$count = count($modules);
$i = 0;
?>
<?php if($count > 0) : ?>
	<div class="proopc-cart-modules">
		<?php foreach($modules as $module) : ?>
			<?php if(!empty($module->moduleHtml)) : ?>
				<?php $i++; ?>
				<div class="proopc-row">
					<div class="cart-promo-mod<?php echo ($i == $count) ? ' last' : ''; ?>">
						<?php if($module->showtitle) : ?>
							<h3><?php echo $module->title ?></h3>
						<?php endif; ?>
						<div class="proopc-cart-module">
							<?php echo $module->moduleHtml; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>