<?php
/**
 * This is a custom template for people metabox content on the EE CPT page.
 *
 * Template Args available are:
 * @type $people_type EE_Term_Taxonomy people type taxonomy term object
 * @type $type              EE_Term Term being displayed.
 * @type $people          EE_Person[]  All the published people from the db (@todo need to do paging/filtering here)
 * @type $assigned_people EE_Person[]  Currently assigned persons for this display.
 * @type $create_person_link string     URL to create a new person.
 */

?>
<div id="cpt_to_people_container_<?php echo $people_type->get('term_taxonomy_id'); ?>">
    <p class="description"><?php echo $people_type->get('description'); ?></p>
    <?php if ( empty( $people ) ) { ?>
        <?php printf( __( 'There are no people in the system. Go ahead and %screate one now%s.', 'event_espresso' ), '<a href="' . $create_person_link . '" target="_blank">', '</a>' ); ?>
    <?php } else { ?>
        <table class="people-to-cpt-table">
            <thead>
                <tr>
                    <th></th>
                    <th><?php _e('Order', 'event_espresso'); ?></th>
                </tr>
            </thead>
            <?php
            $assigned_people_ids = array();
            $order_count = 0;
            $row_count = 0;
            $current_user = get_current_user_id();
            $admin_roles = array('Administrator', 'custom_role');

            // Get the term associated with the current metabox
            $tax_id = $people_type->get('term_taxonomy_id');
            // Get array of people listed in that term
            $term_people = get_objects_in_term( $tax_id, 'espresso_people_type' );
            // First we do the currently assigned people and list first.
            foreach ( $assigned_people as $assigned_person ) {
            ?>
            <tr>
                <td>
                    <label class="selectit">
                        <input value="<?php echo $assigned_person->ID(); ?>" type="checkbox" name="people_to_cpt[<?php echo $tax_id; ?>][<?php echo $row_count; ?>][PER_ID]" id="people-to-cpt-<?php echo $tax_id; ?>" checked="checked"> <?php echo $assigned_person->full_name(); ?>
                    </label>
                </td>
                <td>
                    <input class="PER_order" id="people-to-cpt-order-<?php echo $tax_id; ?>" value="<?php echo $order_count; ?>" type="text" name="people_to_cpt[<?php echo $tax_id; ?>][<?php echo $row_count; ?>][PER_order]">
                </td>

            </tr>
            <?php $assigned_people_ids[] = $assigned_person->ID();  $order_count++; $row_count++; } ?>
            <?php
            // Next we loop through ALL people
            foreach ( $people as $person ) {
                // Find author of person
                $author = $person->wp_user();
                $per_id = $person->ID();
                // Grab list of terms associated with the person
                $per_terms = get_the_terms( $per_id, 'espresso_people_type' );

                // Show all people in each type for admins
                // This shouldn't be a role name, but it depends on what capabilities you've set for other roles
                // current_user_can('administrator') check works for most, but not all of cases
                if( current_user_can('administrator') ){
                    if( !in_array( $per_id, $assigned_people_ids) && in_array( $per_id, $term_people) ) { ?>
            <tr>
                <td>
                    <label class="selectit">
                        <input value="<?php echo $per_id; ?>" type="checkbox" name="people_to_cpt[<?php echo $tax_id; ?>][<?php echo $row_count; ?>][PER_ID]" id="people-to-cpt-<?php echo $tax_id; ?>"> <?php echo $person->full_name(); ?>
                    </label>
                </td>
                <td>
                    <input class="PER_order" id="people-to-cpt-order-<?php echo $tax_id; ?>" value="" type="text" name="people_to_cpt[<?php echo $tax_id; ?>][<?php echo $row_count; ?>][PER_order]">
                </td>
            </tr>
            <?php
                   }
                // Display for everyone else
               } else {
                // If person is not already associated AND person is created by the current user AND person is in the current term's array
                if ( !in_array( $per_id, $assigned_people_ids ) && $author == $current_user && in_array( $per_id, $term_people) ) { ?>
            <tr>
                <td>
                    <label class="selectit">
                        <input value="<?php echo $per_id; ?>" type="checkbox" name="people_to_cpt[<?php echo $tax_id; ?>][<?php echo $row_count; ?>][PER_ID]" id="people-to-cpt-<?php echo $tax_id; ?>"> <?php echo $person->full_name(); ?>
                    </label>
                </td>
                <td>
                    <input class="PER_order" id="people-to-cpt-order-<?php echo $tax_id; ?>" value="" type="text" name="people_to_cpt[<?php echo $tax_id; ?>][<?php echo $row_count; ?>][PER_order]">
                </td>
            </tr>
            <?php } }
                $row_count++;
             } ?>
        </table>
    <?php } ?>
</div>
