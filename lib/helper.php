<?php

/**
 * This file is part of the YConverter package.
 *
 * @author (c) Yakamara Media GmbH & Co. KG
 * @author Thomas Blum <thomas.blum@yakamara.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function yconverterGetSortedSlices($articleId, $clang = false, $moduleId = 0, $revision = 0) {
    $slices = array();
    $slicesTmp = \OOArticleSlice::getSlicesForArticle($articleId, $clang, $revision);
    if ($slicesTmp) {
        if (is_array($slicesTmp)) {
            $sliceMap = array();
            $sliceRefMap = array();
            foreach ($slicesTmp as $slice) {
                $sliceMap[$slice->getId()] = $slice;
                $sliceRefMap[$slice->_re_article_slice_id] = $slice->getId();
            }
            $nextSlice = $sliceMap[$sliceRefMap[0]];
            while ($nextSlice) {
                $slices[] = $nextSlice;
                if (!isset($sliceRefMap[$nextSlice->getId()])) {
                    break;
                }
                $nextSlice = $sliceMap[$sliceRefMap[$nextSlice->getId()]];
            }
        } else {
            $slices = array($slicesTmp);
        }
    }
    if ($moduleId > 0) {
        $moduleSlices = array();
        foreach($slices as $slice) {
            if ($slice->getModuleId() == $moduleId) {
                $moduleSlices[] = $slice;
            }
        }
        return $moduleSlices;
    } else {
        return $slices;
    }
}
