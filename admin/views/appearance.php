<!---------   

=== Theme Designed By: Mark Ballerda
=== Contact Whatsapp: +639205648851
  -----------> 
 <?php include 'header.php'; ?>
<div class="container">
  <div class="row">
    <?php if( ( route(2) == "themes" && !route(3) ) || route(2) != "themes"  ):  ?>
      <div class="col-md-2 col-md-offset-1">
        <ul class="nav nav-pills nav-stacked p-b">
          <?php foreach($menuList as $menuName => $menuLink ): ?>
            <li class="appearance_menus <?php if( $route["2"] == $menuLink ): echo "active"; endif; ?>">
              <a href="<?=site_url("admin/appearance/".$menuLink)?>"><?=$menuName?>
                <?php
                if( $menuLink == "themes"): 
                  echo '<span class="badge badge-primary ft">'.$themesc.'</span>';
                endif;
                if( $menuLink == "pages"): 
                  echo '<span class="badge badge-primary ft">'.$pagesc.'</span>';
                endif;
                if( $menuLink == "news"): 
                  echo '<span class="badge badge-primary ft">'.$newsc.'</span>';
                endif;
                if( $menuLink == "blog"): 
                  echo '<span class="badge badge-primary ft">'.$blogc.'</span>';
                endif;
                if( $menuLink == "language"): 
                  echo '<span class="badge badge-primary ft">'.$languagec.'</span>';
                endif;
                if( $menuLink == "integrations"): 
                  echo '<span class="badge badge-primary ft">'.$integrationsc.'</span>';
                endif;
                if( $menuLink == "files"): 
                  echo '<span class="badge badge-primary ft">'.$filesc.'</span>';
                endif;
                ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="col-md-9">
      <?php 
      if( $access ):
        include admin_view('appearance/'.route(2));
      else:
        include admin_view('settings/access');
      endif;
      ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>