  <?php

  global $GJ_Maps;

  require_once('db.php');

    if(isset($_POST['gj_hidden']) && $_POST['gj_hidden'] == 'Y') {
      $icon = null;
      if ($_FILES['icon']) {
        $upload = wp_handle_upload($_FILES['icon'], array('test_form'=>false));
        if (isset($upload['url'])) {
          $icon = $upload['url'];
        }
      }

      //Form data sent
        global $post;
        if (isset($_POST['id'])) {

          if (isset($_POST['delete'])) {
            //Delete Selected cat
            deleteCat($_POST['id']);
          } else {

            //Update existing cat
            $cat = array();
            $defaultCat = array(
              "id" => "1",
              "name" => "category",
              "color" => "#000000",
              "hide_list" => 0,
              "filter_resist" => 0,
              "icon" => NULL
              );

            foreach ($_POST as $key=>$value) {
              if ($key !== 'gj_hidden') {
                $cat[$key] = stripslashes($value);
              }
            }
            $cat['icon'] = $icon;
            $cat = array_merge($defaultCat, $cat);
            editCat($cat);
          }
 
        } else {
          //Add new Category
          $cat = array();
          foreach ($_POST as $key=>$value) {
            if ($key !== 'gj_hidden') {
              $cat[$key] = $value;
            }
          }
          $cat['icon'] = $icon;
          saveCat($cat);
        }

    }

    $cat = $GJ_Maps->get_cat();

    ?>
    <div class="wrap">
      <?php echo "<h2>" . __( 'GJ Maps - Categories', 'gj_trdom' ) . "</h2>"; ?>

      <h4>Add New</h4>
        <form name="gj_form" method="post" enctype="multipart/form-data"  action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="gj_hidden" value="Y"/>
          <input type="text" name="name" placeholder="Name"/>
          <input type="text" name="color" id="newColor" class="color-picker"/>
          <label for="cat">Icon: 
          <input type="file" name="icon" value="<?php echo $object->icon; ?>"/>
          </label>

          <p class="submit"><input type="submit" value="<?php _e('Add Category', 'gj_trdom' ) ?>" /></p>
        </form>


      <h4>Edit Categories</h4>

        <?php

        foreach ($cat as $index=>$object) {
        ?>
        
          <form name="gj_form" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
          <input type="hidden" name="gj_hidden" value="Y"/>
          <input type="hidden" name="id" value="<?php echo $object->id; ?>"/>

          <label for="name">Name: 
          <input type="text" name="name" placeholder="Name" value="<?php echo $object->name; ?>"/>
          </label>

          <label for="cat">Color: 
          <input type="text" name="color" class="color-picker" id="<?php $object->id; ?>Color" value="<?php echo $object->color; ?>"/>
          </label>

          <label for="cat">Icon: <img src="<?php echo $object->icon; ?>"/>
          <input type="file" name="icon" value="<?php echo $object->icon; ?>"/>
          </label>


          <br />

          <label for="option-hide">Hide from list? :
          <input type="checkbox" name="hide_list" value="1" <?php if ($object->hide_list) echo 'checked'; ?>/>
          </label>

          <label for="option-nofilter">Resist filtering? :
          <input type="checkbox" name="filter_resist" value="1" <?php if ($object->filter_resist) echo 'checked'; ?>/>
          </label>

          <label for="delete">Delete this Category? : 
          <input type="checkbox" name="delete"/>
          </label>

          <br />
          <input type="submit" value="<?php _e('Submit Changes', 'gj_trdom' ) ?>" />

          </form>

        <br /><hr /><br />
        <?php } ?>

    </div>
