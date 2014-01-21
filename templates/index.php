<!doctype html>
<html>
 <head>
  <title><?=$this->__info['title']?></title>
  <link rel="stylesheet" type="text/css" href="/css/style.css">
  <?php foreach ($this->__assets['css'] as $css) { ?>
  <link rel="stylesheet" type="text/css" href="/css/<?=$css?>">
  <?php } ?>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <?php foreach ($this->__assets['js'] as $js) { ?>
  <script type="text/javascript" src="/js/<?=$js?>"></script>
  <?php } ?>
 </head>
 <body>
  <div class="header">
   <div class="inner">
    <h1><a href="http://www.btctarget.com/">Bitcoin Target</a></h1>
   </div>
  </div>
  <div class="content">
   <div class="inner">
    <?=$view_output?>
   </div>
  </div>
  <div class="footer">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- btctarget: Footer -->
<ins class="adsbygoogle"
     style="display:block;width:728px;height:90px;margin:0 auto"
     data-ad-client="ca-pub-2619534444109891"
     data-ad-slot="6978539657"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
   <div class="inner">
    <p>Donate to keep the site running! 1Fo7ieiaQmvHTnFWPkw3zQcpWuantdgU7f</p>
   </div>
  </div>
 </body>
</html>
