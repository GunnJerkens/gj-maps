<?php get_header(); ?>

<div class="contentPage" class="clearfix">
<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      

        <?php the_content(); ?>
            

    </article>

<?php endwhile; ?>

</div>

<div class="sidebar">
<?php 
if ( dynamic_sidebar('subpage_1') ) : 
else : 
?>
<?php endif; ?>
</div>

<?php get_footer(); ?>