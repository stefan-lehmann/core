<?php

global $CJO, $I18N;

if ("CJO_LINK_ID[1]" && "CJO_VALUE[2]") {

	if ($CJO['CONTEJO']) {
		$master = OOArticle::getArticleByID("CJO_LINK_ID[1]");
		$link = $I18N->msg('alias_of_article').': <a href="index.php?page=edit&subpage=content&article_id=CJO_LINK_ID[1]&mode=edit&clang='.$CJO['CUR_CLANG'].'&ctype=CJO_VALUE[1]"
				 style="text-decoration: underline!important">'.$master->getName().'</a> (ID=CJO_LINK_ID[1])';
	}

	if (OOArticle::isOnline("CJO_LINK_ID[1]")) {

		if ($CJO['CONTEJO']) {
			print '<p class="accept">'.$link.'</p>';
		}

		$article = new cjoArticle();
        $article->setArticleId("CJO_LINK_ID[1]");
        $article->setCLang("CJO_VALUE[3]" == "" ? $CJO['CUR_CLANG'] : "CJO_VALUE[3]");

		if ("CJO_VALUE[2]" != "all") {
			$article->setSliceId("CJO_VALUE[2]");
		}
		echo $article->getArticle("CJO_VALUE[1]");
	}
	else {
		if ($CJO['CONTEJO']) {
			print '<p class="error">'.$link.'. '.$I18N->msg('msg_article_offline').'</p>';
		}
	}
} elseif ($CJO['CONTEJO']) {
	print '<p class="error">'.$I18N->msg('msg_no_alias_settings').'</p>';
}
?>