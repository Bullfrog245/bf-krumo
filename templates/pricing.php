<?php
global $wpdb;

$programs = $wpdb->get_results("
	SELECT post_id
	FROM wp_postmeta
	WHERE meta_key = '_program_meta_end_date'
		AND meta_value > '2013-11-01'
	ORDER BY meta_value ASC
");

$lodgings = array();
foreach ( $programs as $program )
{
	$data = $wpdb->get_results("
		SELECT meta_value
		FROM `wp_postmeta`
		WHERE meta_key = '_program_meta_Pricing'
			AND post_id = '$program->post_id'
	");
	$data = maybe_unserialize( $data[0]->meta_value );

	if ( $data['structure'] == 'lodging' )
	{
		$lodgings[$program->post_id] = $data;
	}
}


$update = $wpdb->get_var("
	SELECT date
	FROM bf_pricing
	ORDER BY date DESC
	LIMIT 1
");

$date = date( 'Y-m-d' ) . ' 00:00:00';

if ( is_null( $update ) || strtotime( $update ) < strtotime( $date ) )
{
	echo '<p>Prices Logged</p>';
	foreach ( $lodgings as $id => $data )
	{
		$wpdb->insert(
			'bf_pricing',
			array(
				'post_id' => $id,
				'date' => $date,
				'data' => serialize( $data ),
			),
			array(
				'%d',
				'%s',
				'%s',
			)
		);
	}
}






$i = 0;
foreach ( $lodgings as $id => $data )
{
	$post = get_post( $id, 'OBJECT' );
	//krumo( $post );
	if ( $post->post_status != 'publish' && $post->post_status != 'draft' )
		continue;

	$log = $wpdb->get_results("
		SELECT data
		FROM bf_pricing
		WHERE post_id = '$id'
		ORDER BY date DESC
		LIMIT 1
	");
	$log = maybe_unserialize( $log[0]->data );
	$log = $log['lodging']['prices'];

	$enabled = $data['lodging']['enable'];
	$prices = $data['lodging']['prices'];


	//krumo( $enabled, $prices, $log );

	$style = "width: 48%; float: left; margin-bottom: 30px;";
	if ( $i % 2 == 0 )
	{
		$style .= " clear: left; margin-right: 4%;";
	}
?>
	<table style="<?php echo $style ?>">
		<tr style="text-align: left;">
			<th><?php echo $post->post_title ?></th>
			<th><a target="_blank" href="/wp-admin/post.php?post=<?php echo $id ?>&action=edit">EDIT</a></th>
		</tr>

<?php
	foreach ( $prices as $pid => $price ):
		if ( in_array( $price['lodging_id'], $enabled ) ):

			$oval = '';
			foreach ( $log as $key => $value )
			{
				if ( $value['description'] == $price['description'] )
				{
					$oval = $value;
				}
			}
			$substyle = 'width: 45px;';
			if ( !empty( $oval ) && $price['price'] != $oval['price'] )
			{
				$substyle .= ' color: #c00;';
			}
?>
		<tr>
			<td><?php echo $price['description'] ?></td>
			<td style="<?php echo $substyle ?>">$<?php echo $price['price'] ?></td>
			<td style="<?php echo $substyle ?>"><small>$<?php echo $oval['price'] ?></small></td>
		</tr>
<?php
	endif;
	endforeach;
?>

		<tr style="text-align: right;">
			<td colspan="2"><small>Last Modified: <?php echo $post->post_modified ?></small></td>
		</tr>
	</table>

<?php
	$i++;
	//krumo( $enabled, $prices );
}