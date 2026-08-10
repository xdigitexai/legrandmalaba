<!---------   

=== Theme Designed By: Mark Ballerda
=== Contact Whatsapp: +639205648851
 ----------->  


<?php if( !route(3) ): ?> 
<style>
.custom-select {
    border-radius: 5px;
    border: 1px solid #007bff;
}
.btn-success {
    background-color: #28a745;
    border-color: #218838;
    color: #fff;
}
.dropdown-menu {
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
}
#fullscreen {
  width: 100%;
  height: 100vh; 
  overflow: auto; 
  background: #282a36; 
  color: #f8f8f2;
  padding: 20px;
}
.CodeMirror {
  height: calc(100vh - 60px); 
}
.fullScreenButton {
  cursor: pointer;
}
/* Fullscreen styles */
:fullscreen #fullscreen {
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 20px;
}
#fullscreen:fullscreen .CodeMirror {
  height: 100%;
}

/* === New Styles for Theme Cards === */
.theme-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill,minmax(300px,1fr));
  gap: 20px;
}
.theme-card {
  border: 1px solid #ddd;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: transform 0.2s ease;
}
.theme-card:hover {
  transform: translateY(-3px);
}
.theme-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}
.theme-card-body {
  padding: 15px;
}
.theme-card-footer {
  display: flex;
  justify-content: space-between;
  padding: 10px 15px;
  background: #f8f9fa;
}
.theme-active {
  border: 2px solid #28a745 !important;
}
</style>  

<div class="col-md-12">
  <form method="POST">
    <a href="https://wa.me/639205648851?text=Hello%20there! I want to Avail Theme." class="btn btn-primary mb-3">
      <i class="fa fa-paint-brush"></i> Add New Theme
    </a>
  </form>

  <!-- Theme Grid -->
  <div class="theme-grid">
  <?php foreach($themes as $theme): ?>
    <div class="theme-card <?php if( $settings["site_theme"] == $theme["theme_dirname"] ) echo 'theme-active'; ?>">
      <!-- Theme Preview Image -->
      <img src="<?php echo site_url("app/views/".$theme["theme_dirname"]."/preview.jpg"); ?>" alt="<?php echo $theme["theme_name"]; ?> Preview">

      <div class="theme-card-body">
        <h5>
          <?php echo $theme["theme_name"]; ?>
          <?php if( $settings["site_theme"] == $theme["theme_dirname"] ): ?>
            <span class="badge">Active</span>
          <?php endif; ?>
        </h5>
        <small>Last modified: <?php echo $theme["last_modified"]; ?></small><br>
        <small style="font-weight: bold; color: green;">(<?php echo $theme["theme_dirname"]; ?>)</small>

        <!-- Existing Colour Change Dropdowns -->
        <?php if( $settings["site_theme"] == $theme["theme_dirname"] ): ?>
 
<?php 
if ($theme["colour"] == "2" && $theme["theme_dirname"] == "Simplify"):
    echo '
    <div class="dropdown pull-right">
      <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
        Colour Change <span class="caret"></span>
      </button>
      <ul class="dropdown-menu p-3" style="width: 250px;">
        <form action="" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="site_theme_alt" class="font-weight-bold">Select Colour</label>
            <select class="form-control custom-select" name="site_theme_alt" id="site_theme_alt">
              <option value="Red">Red</option>
              <option value="Blue">Blue</option>
              <option value="Lime">Lime</option>
              <option value="Grapes">Grapes</option>
              <option value="Dark">Dark</option>
              <option value="Cyan">Cyan</option>
              <option value="Coral">Coral</option>
              <option value="Green">Green</option>
              <option value="Grey">Grey</option>
              <option value="Lilac">Lilac</option>
              <option value="Orange">Orange</option>
            </select>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-success btn-sm">Update</button>
          </div>
        </form>
      </ul>
    </div>';
endif;


if ($theme["colour"] == "2" && $theme["theme_dirname"] == "Eternity"):
    echo '
    <div class="dropdown pull-right">
        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
            Colour Change <span class="caret"></span>
        </button>
        <ul class="dropdown-menu p-3" style="width: 250px;">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="site_theme_eternity" class="font-weight-bold">Select Colour</label>
                    <select class="form-control custom-select" name="site_theme_alt" id="site_theme_eternity">
                        <option value="lilac">Eternity Lilac</option>
                        <option value="coral">Eternity Coral</option>
                        <option value="azure">Eternity Azure</option>
                        <option value="grey">Eternity Grey</option>
                        <option value="lime">Eternity Lime</option>
                        <option value="navy">Eternity Navy</option>
                        <option value="pink">Eternity Pink</option>
                        <option value="raspberry">Eternity Raspberry</option>
                        <option value="cyan">Eternity Cyan</option>
                        <option value="purple">Eternity Purple</option>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                </div>
            </form>
        </ul>
    </div>';
endif;



if ($theme["colour"] == "2" && $theme["theme_dirname"] == "Simplifyvyeallow"):
    echo '
    <div class="dropdown pull-right">
        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
            Colour Change <span class="caret"></span>
        </button>
        <ul class="dropdown-menu p-3" style="width: 250px;">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="site_theme_alt" class="font-weight-bold">Choose a Colour</label>
                    <select class="form-control custom-select" name="site_theme_alt" id="site_theme_alt">
                        <option value="Red">Red</option>
                        <option value="Blue">Blue</option>
                        <option value="Lime">Lime</option>
                        <option value="Grapes">Grapes</option>
                        <option value="Dark">Dark</option>
                        <option value="Cyan">Cyan</option>
                        <option value="Coral">Coral</option>
                        <option value="Green">Green</option>
                        <option value="Grey">Grey</option>
                        <option value="Lilac">Lilac</option>
                        <option value="Orange">Orange</option>
                    </select>
                </div> 
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                </div>
            </form>
        </ul>
    </div>';
endif;


if ($theme["colour"] == "2" && $theme["theme_dirname"] == "pitchy"):
    echo '
    <div class="dropdown pull-right">
        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
            Colour Change <span class="caret"></span>
        </button>
        <ul class="dropdown-menu p-3" style="width: 250px;">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="site_theme_pitchy" class="font-weight-bold">Select Colour</label>
                    <select class="form-control custom-select" name="site_theme_alt" id="site_theme_pitchy">
                        <option value="green">Clementine Green</option>
                        <option value="parrot">Clementine Parrot</option>
                        <option value="orange">Clementine Orange</option>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                </div>
            </form>
        </ul>
    </div>';
endif;

if ($theme["colour"] == "2" && $theme["theme_dirname"] == "smmgen"):
    echo '
    <div class="dropdown pull-right">
        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
            Colour Change <span class="caret"></span>
        </button>
        <ul class="dropdown-menu p-3" style="width: 250px;">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="site_theme_smmgen" class="font-weight-bold">Select Colour</label>
                    <select class="form-control custom-select" name="site_theme_alt" id="site_theme_smmgen">
                        <option value="blue">Blue</option>
                        <option value="green">Green</option>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                </div>
            </form>
        </ul>
    </div>';
endif;

 
				 
endif; ?>
      </div>

      <div class="theme-card-footer">
        <?php if ($settings["site_theme"] != $theme["theme_dirname"]): ?>
          <a href="<?php echo site_url('admin/appearance/themes/active/' . $theme["id"]) ?>" class="btn btn-success btn-sm">
            <i class="fa-solid fa-toggle-on"></i> Activate
          </a>
        <?php endif; ?>
        <a href="<?php echo site_url('admin/appearance/themes/'.$theme["id"]) ?>" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-pen-to-square"></i> Edit
        </a>
        <a class="btn btn-danger btn-sm custom_method"
           data-title="Delete Theme?"
           data-message="Are you sure? This action can't be reversed. After deleting, please refresh the page to see the changes."
           data-url="<?php echo site_url('admin/appearance/themes/delete/' . $theme["id"]) ?>">
          <i class="fa-solid fa-trash"></i> Delete
        </a>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>
 
<?php elseif( route(3) ):?>
  <div class="col-md-12">
    <div class="panel">
      <div class="panel-heading edit-theme-title">
        <strong><?php echo $theme["theme_name"] ?> (<?php echo $theme["theme_dirname"]; ?>)</strong> edit the theme named
      </div>
      <div class="row">
        <div class="col-md-3 padding-md-right-null">
          <div class="panel-body edit-theme-body">
            <div class="twig-editor-block">
              <?php
                $layouts = [
                  "HTML" => [
                      "account.twig", "addfunds.twig", "api.twig", "blog.twig",
                      "blogpost.twig", "child-panels.twig", "dripfeeds.twig",
                      "faq.twig", "footer.twig", "header.twig", "login.twig",
                      "neworder.twig", "open_ticket.twig", "orders.twig",
                      "refer.twig", "refill.twig", "resetpassword.twig",
                      "setnewpassword.twig", "services.twig", "signup.twig",
                      "subscriptions.twig", "terms.twig", "tickets.twig",
                      "updates.twig"
                  ]
                ];
                foreach ($layouts as $style => $layout):
                  echo '<div class="twig-editor-list-title" data-toggle="collapse" href="#folder_'.$style.'"><span class="fa fa-folder-open"></span>'.$style.'</div><ul class="twig-editor-list collapse in" id="folder_'.$style.'">';
                  foreach ($layouts[$style] as $layout):
                    $active = ($lyt == $layout) ? ' class="active file-modified" ' : '';
                    echo '<li '.$active.'><a href="'.site_url('admin/appearance/themes/'.$theme["id"]).'?file='.$layout.'">'.$layout.'</a></li>';
                  endforeach;
                  echo '</ul>';
                endforeach;
              ?>
            </div>
          </div>
        </div>
        <div class="col-md-9 padding-md-left-null edit-theme__block-editor">
          <?php if( !$lyt ): ?>
            <div class="panel-body">
              <div class="alert alert-info" role="alert">
                Select a file from the left sidebar to start editing.
              </div>
            </div>
          <?php else: ?>
            <div id="fullscreen">
              <?php
                $file = fopen($fn, "r");
                $size = filesize($fn);
                $text = fread($file, $size);
                fclose($file);
              ?>
             
          <div class="row">
    <div class="col-md-8">
      <strong class="edit-theme-filename"><?=$dir."/".$lyt?></strong>
    </div>
    <div class="col-md-4 text-right">
      <button class="btn btn-xs btn-default fullScreenButton" onclick="toggleFullScreen()">
        <span class="glyphicon glyphicon-fullscreen"></span> Full Screen
      </button>
    </div>
  </div>
              
                <form action="<?php echo site_url("admin/appearance/themes/".$theme["id"]."?file=".$lyt) ?>" method="post" class="twig-editor__form">
                  <textarea id="code" name="code" class="codemirror-textarea"><?=$text;?></textarea>
                  <div class="edit-theme-body-buttons text-right">
                      
                    <button class="btn btn-primary click">Save</button>
                  </div>
                </form>
            </div>
          <?php endif; ?>

          </div>
        </div>
    </div>
  </div>
<?php endif; ?>
  


<!-- CodeMirror Integration -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.10/mode/htmlmixed/htmlmixed.min.js"></script>



  <!---------   

=== Theme Designed By: Mark Ballerda
=== Contact Whatsapp: +639205648851
 ----------->  