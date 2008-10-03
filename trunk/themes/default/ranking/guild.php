<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2>Guild Ranking</h2>
<h3>
	Top <?php echo number_format($limit=(int)Flux::config('CharRankingLimit')) ?> Guilds
	on <?php echo htmlspecialchars($server->serverName) ?>
</h3>
<?php if ($guilds): ?>
	<table class="horizontal-table">
		<tr>
			<th>Rank</th>
			<th colspan="2">Guild Name</th>
			<th>Level</th>
			<th>Members</th>
			<th>Experience</th>
		</tr>
		<?php for ($i = 0; $i < $limit; ++$i): ?>
		<tr>
			<td align="right"><?php echo number_format($i + 1) ?></td>
			<?php if (isset($guilds[$i])): ?>
			<td width="24"><img src="<?php echo $this->emblem($guilds[$i]->guild_id) ?>" /></td>
			<td><strong><?php echo htmlspecialchars($guilds[$i]->name) ?></strong></td>
			<td><?php echo number_format($guilds[$i]->guild_lv) ?></td>
			<td><?php echo number_format($guilds[$i]->members) ?></td>
			<td><?php echo number_format($guilds[$i]->exp) ?></td>
			<?php else: ?>
			<td colspan="8"></td>
			<?php endif ?>
		</tr>
		<?php endfor ?>
	</table>
<?php else: ?>
<p>No guilds found. <a href="javascript:history.go(-1)">Go back</a>.</p>
<?php endif ?>