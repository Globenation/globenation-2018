<tr<?php echo isset( $_addon['class'] ) ? ' class="' . $_addon['class'] . '"' : ''; ?>><th><?php echo $_addon['title']; ?></th>
	<td><?php echo $_addon['desc']; ?></td>
	<td><?php if ( isset( $_addon['link'] ) && $_addon['link'] ) : ?>
	<a class="button <?php echo ( isset( $_addon['link_primary'] ) && isset( $_addon['link_primary'] ) ) ? 'button-primary' : 'button-secondary'; ?>" href="<?php echo $_addon['link']; ?>" target="_blank">
								<?php
								echo $link_title;
								?>
	</a>
		<?php
	endif;
?>
	</td>
</tr>
