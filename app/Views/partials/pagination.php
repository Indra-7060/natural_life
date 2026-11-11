<?php
/** @var \CodeIgniter\Pager\PagerRenderer $pager */
$pager->setSurroundCount(2);
if ($pager->getPageCount() > 1): ?>
<nav aria-label="Pagination">
    <ul class="pager">
        <?php if ($pager->hasPreviousPage()): ?>
            <li><a href="<?= $pager->getPreviousPage() ?>" aria-label="Previous page"><i class="bi bi-arrow-left"></i></a></li>
        <?php endif ?>
        <?php foreach ($pager->links() as $link): ?>
            <li class="<?= $link['active'] ? 'is-active' : '' ?>"><a href="<?= $link['uri'] ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>><?= $link['title'] ?></a></li>
        <?php endforeach ?>
        <?php if ($pager->hasNextPage()): ?>
            <li><a href="<?= $pager->getNextPage() ?>" aria-label="Next page"><i class="bi bi-arrow-right"></i></a></li>
        <?php endif ?>
    </ul>
</nav>
<?php endif ?>
