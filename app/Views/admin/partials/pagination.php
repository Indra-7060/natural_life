<?php
/** @var \CodeIgniter\Pager\PagerRenderer $pager */
$pager->setSurroundCount(2);
if ($pager->getPageCount() > 1): ?>
<ul class="pager" aria-label="Pagination">
    <?php if ($pager->hasPreviousPage()): ?><li><a href="<?= $pager->getPreviousPage() ?>" aria-label="Previous"><i class="bi bi-chevron-left"></i></a></li><?php endif ?>
    <?php foreach ($pager->links() as $link): ?><li class="<?= $link['active'] ? 'is-active' : '' ?>"><a href="<?= $link['uri'] ?>"><?= $link['title'] ?></a></li><?php endforeach ?>
    <?php if ($pager->hasNextPage()): ?><li><a href="<?= $pager->getNextPage() ?>" aria-label="Next"><i class="bi bi-chevron-right"></i></a></li><?php endif ?>
</ul>
<?php endif ?>
