<div class="target">
 <h2>Finding block <?=$this->data['nextblock']?> at difficulty <?=number_format((int)$this->data['difficulty'])?></h2>
 <ul>
  <li class="targethash"><?=$this->data['target']?></li>
  <li class="currhash"></li>
  <li class="currtime"></li>
 </ul>
 <p><em>Bitcoin Target is a visual aid only, and does not perform any calculations.</em></p>
 <p>The Bitcoin blockchain is built by taking a list of the transactions since the last block, along with the hash of the last block, and trying to build a hash that's numerically lower than the target (in black above). Networked devices that perform the block finding, called "miners", can twiddle a value called the "nonce" until the hash of the whole block is low enough, and let the rest of the network know when that happens.</p>
 <p>Bitcoin Target picks up the broadcasts of found blocks, and turns the current hash green when one has been found. Mining performed by the network, broadcasts taken from <a href="http://blockchain.info/">blockchain.info</a>.</p>
</div>
<div class="blocks">
 <h2>Latest blocks</h2>
 <ul>
  <?php foreach ($this->data['blocks'] as $blk): ?>
  <li>
   <a href="http://blockchain.info/block-index/<?=$blk['hash']?>"><?=$blk['height']?></a>,
   <span class="time" rel="<?=$blk['time']?>"><?=date('jS M Y, H:i', $blk['time'])?></span>
  </li>
  <?php endforeach; ?>
 </ul>
</div>
<div class="clear"></div>
<div id="initialdata"><?=json_encode($this->data);?></div>
