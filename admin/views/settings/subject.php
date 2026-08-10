<?php if( !route(4) ): ?>

<style>
/* 3D Card Container */
.card {
    background: #fefefe;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 6px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 8px 20px rgba(0,0,0,0.12);
}

/* Labels */
.control-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

/* Inputs & Selects 3D style */
.form-control, .form-select {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 12px 15px;
    background: #fdfdfd;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.03), inset -2px -2px 5px rgba(255,255,255,0.8);
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.05), inset -2px -2px 5px rgba(255,255,255,0.85),
                0 0 10px rgba(0,123,255,0.3);
    outline: none;
}

/* Buttons 3D */
.btn-primary {
    background: linear-gradient(145deg, #4e9cff, #187bff);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    padding: 14px 0;
    box-shadow: 0 6px 15px rgba(0,123,255,0.4);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(145deg, #187bff, #4e9cff);
    box-shadow: 0 10px 20px rgba(0,123,255,0.5);
}

/* Alerts / Section Containers */
.alert {
    background: #f6f8fc;
    border-radius: 12px;
    padding: 20px;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.03), inset -2px -2px 5px rgba(255,255,255,0.9);
    margin-bottom: 20px;
}

/* Image Preview 3D style */
.setting-block__image {
    position: relative;
    display: inline-block;
    margin-left: 10px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 2px 2px 12px rgba(0,0,0,0.1), -2px -2px 12px rgba(255,255,255,0.6);
}

.setting-block__image img {
    border-radius: 12px;
}

/* Tooltip 3D */
.tooltip5 {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.tooltip5 .tooltiptext5 {
    visibility: hidden;
    width: 200px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 6px 10px;
    position: absolute;
    z-index: 1;
    bottom: 130%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.tooltip5:hover .tooltiptext5 {
    visibility: visible;
    opacity: 1;
}

/* Divider */
hr {
    border: 0;
    height: 1px;
    background: #e0e0e0;
    margin: 30px 0;
}

/* Textarea 3D */
textarea.form-control {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 12px;
    background: #fdfdfd;
    box-shadow: inset 2px 2px 6px rgba(0,0,0,0.03), inset -2px -2px 6px rgba(255,255,255,0.8);
    transition: all 0.3s ease;
}

textarea.form-control:focus {
    border-color: #007bff;
    box-shadow: inset 2px 2px 6px rgba(0,0,0,0.05), inset -2px -2px 6px rgba(255,255,255,0.85),
                0 0 10px rgba(0,123,255,0.3);
    outline: none;
}
</style>

<div class="col-md-9">

                      <?php if( $success ): ?>
          <div class="alert alert-success "><?php echo $successText; ?></div>
        <?php endif; ?>
           <?php if( $error ): ?>
          <div class="alert alert-danger "><?php echo $errorText; ?></div>
        <?php endif; ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row settings-menu__row">
                        <div class="col-md-3">
                            <div class="settings-menu__title">Titles</div>
                            <div class="settings-menu__description">Support request options.</div>
                        </div>
                        <div class="col-md-9">
                            <div class="dd">
                                <ol class="dd-list ui-sortable">
                                       <?php foreach($subjectList as $subject): ?>
                                        <li class="dd-item ui-sortable-handle">
                                            <div class="dd-handle"><?php echo $subject["subject"]?></div>
                                            <div class="settings-menu__action">
                                                <?php if($subject["auto_reply"] == 1):
                                                echo'<i class="fas fa-magic"></i> ';
                                                endif; ?>
                                                <a href="<?php echo site_url('admin/settings/subject/edit/'.$subject["subject_id"].'') ?>" class="btn btn-default btn-xs edit-modal-menu">Edit</a>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                                                           
                                                                    </ol>
                            </div>
                            <a href="javascript:;" onclick="showMe('gizlebeni');" class="btn btn-default m-b add-modal-menu">Create New Title</a>
                        </div>
                    </div>

                </div>
            </div>
      
        
         <div class="panel panel-default" id="gizlebeni" style="display: none;">
    <div class="panel-body">

         <form action="<?php echo site_url('admin/settings/subject') ?>" method="post" enctype="multipart/form-data">
             
                     <div class="form-group relative">
         
          <label for="" class="control-label">Support Title</label>
          <input type="text" class="form-control" name="subject">
        </div>
        
<div class="form-group">
               <label class="control-label">Auto Answer</label>
<select class="form-control" name="auto_reply">
    <option value="0" selected>Closed</option>
    <option value="1">Active</option>
</select>            </div>	          

            <div class="form-group">
               <label class="control-label">Message to Auto Reply</label>
               <textarea class="form-control" rows="5" name="content"></textarea>
            </div>	  
           <p>Automatic reply when a new support request is created under this topic.</p> 
            <hr>

            <button type="submit" class="btn btn-primary w-100" >Create</button>
         </form>

</div> </div>



</div>

<script type="text/javascript">
function showMe(blockId) {
     if ( document.getElementById(blockId).style.display == 'none' ) {
          document.getElementById(blockId).style.display = ''; }
else if ( document.getElementById(blockId).style.display == '' ) {
          document.getElementById(blockId).style.display = 'none'; }
}
</script>


<?php elseif( route(3) == "edit" ): ?>
<div class="col-md-9">
    <a href="/admin/settings/subject" class="details_backButton btn btn-link"><span>‹</span> Back</a>

            <?php if( $success ): ?>
          <div class="alert alert-success "><?php echo $successText; ?></div>
        <?php endif; ?>
           <?php if( $error ): ?>
          <div class="alert alert-danger "><?php echo $errorText; ?></div>
        <?php endif; ?>

                 
         <div class="panel panel-default">
    <div class="panel-body">

         <form action="<?php echo site_url('admin/settings/subject/edit/'.route(4)) ?>" method="post" enctype="multipart/form-data">
             
                     <div class="form-group relative">
         
          <label for="" class="control-label">Support Title</label>
          <input type="text" class="form-control" name="subject" value="<?=$post["subject"]?>">
        </div>
        
<div class="form-group">
               <label class="control-label">Auto Answer</label>
<select class="form-control" name="auto_reply">
    <option value="0" <?php if($post["auto_reply"] == 0){echo'selected';}
    elseif($post["auto_reply"] == 1){echo'selected'; }?>>Closed</option>
    <option value="1" <?php if($post["auto_reply"] == 1){echo'selected';}
    elseif($post["auto_reply"] == 1){echo'selected'; }?>>Active</option>
</select>            </div>	          

            <div class="form-group">
               <label class="control-label">Message to Auto Reply</label>
               <textarea class="form-control" rows="5" name="content"><?=$post["content"]?></textarea>
            </div>	  
           <p>Automatic reply when a new support request is created under this topic.</p> 
            <hr>

            <button type="submit" class="btn btn-primary">Update</button>

            <a href="<?php echo site_url('admin/settings/subject/delete/'.$post["subject_id"]) ?>" class="btn btn-link pull-right deactivate-integration-btn">
                                Delete
                            </a>

         </form>

</div> </div>


</div> </div> </div> 


<?php endif; ?>
 