<?php
/**
 * Template for when people are displayed with events (is_single());
 * Note: To customize, just copy the template from /public/templates/* and put in your theme folder.
 * @since       1.0.0
 * @package     EE People Addon
 * @subpackage  template
 * @author      Darren Ethier
 */
global $post;
EE_Registry::instance()->load_helper( 'People_View' );
$people = EEH_People_View::get_people_for_event();
if ( $people ) :
?>
<style>
    .eea-people-addon-people-list-ul li {
        clear: both;
        margin: 0 0 1em;
    }
    .eea-people-addon-feature-image {
        float: left;
        margin: 0 1em 1em 0;
    }
</style>
<div class="eea-people-addon-event-people-list-single">
    <?php foreach ( $people as $type => $persons ) : ?>
        <div class="eea-people-addon-people-type-container">
            <?php
                $type_class = ' eea-people-addon-people-type-' . str_replace( ' ', '-', strtolower( $type ) );
            ?>
            <h4 class="eea-people-addon-people-type-label<?php echo $type_class; ?>"><?php echo $type; ?></h4>
            <ul class="eea-people-addon-people-list-ul">
                <?php foreach ( $persons as $person ) : ?>
                    <?php if ( $person instanceof EE_Person ) : ?>
                        <li>
                            <a class="eea-people-addon-link-to-person" href="<?php echo get_permalink( $person->ID() ); ?>" title="<?php printf( __('Click here to view more info about %s', 'event_espresso' ), $person->full_name() ); ?>"><span class="eea-people-addon-person-full-name"><?php echo $person->full_name(); ?></span></a><br>
                            <span class="eea-people-addon-excerpt"><?php echo $person->get('PER_short_bio' ); ?></span>
                            <div class="clear-float"></div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; //end people check ?>
