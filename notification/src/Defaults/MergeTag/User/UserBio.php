<?php
/**
 * User Bio merge tag
 *
 * Requirements:
 * - Trigger property `user_object` or any other passed as
 * `property_name` parameter. Must be an object, preferabely WP_User
 *
 * @package notification
 */

namespace BracketSpace\Notification\Defaults\MergeTag\User;

use BracketSpace\Notification\Defaults\MergeTag\StringTag;

/**
 * User Bio merge tag class
 */
class UserBio extends StringTag {

	/**
	 * Trigger property to get the user data from
	 *
	 * @var string
	 */
	protected $property_name = 'user_object';

	/**
	 * Merge tag constructor
	 *
	 * @since 5.0.0
	 * @param array $params merge tag configuration params.
	 */
	public function __construct( $params = [] ) {

		if ( isset( $params['property_name'] ) && ! empty( $params['property_name'] ) ) {
			$this->property_name = $params['property_name'];
		}

		$args = wp_parse_args(
			$params,
			[
				'slug'        => 'user_bio',
				'name'        => __( 'User bio', 'notification' ),
				'description' => __( 'Developer based in Ontario, Canada', 'notification' ),
				'example'     => true,
				'group'       => __( 'User', 'notification' ),
				'resolver'    => function( $trigger ) {
					return $trigger->{ $this->property_name }->description;
				},
			]
		);

		parent::__construct( $args );

	}

}
