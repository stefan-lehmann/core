<?php

global $CJO, $I18N;

if ("CJO_LINK_ID[1]" && "CJO_VALUE[2]") {

	if (cjoProp::isBackend()) {
		$master = OOArticle::getArticleByID("CJO_LINK_ID[1]");
		$link = cjoI18N::translate('alias_of_article').': <a href="index.php?page=edit&subpage=content&article_id=CJO_LINK_ID[1]&mode=edit&clang='.cjoProp::getClang().'&ctype=CJO_VALUE[1]"
				 style="text-decoration: underline!important">'.$master->getName().'</a> (ID=CJO_LINK_ID[1])';
	}

	if (OOArticle::isOnline("CJO_LINK_ID[1]")) {

		if (cjoProp::isBackend()) {
			print '<p class="accept">'.$link.'</p>';
		}

		$article = new cjoArticle();
        $article->setArticleId("CJO_LINK_ID[1]");
        $article->setCLang("CJO_VALUE[3]" == "" ? cjoProp::getClang() : "CJO_VALUE[3]");

		if ("CJO_VALUE[2]" != "all") {
			$article->setSliceId("CJO_VALUE[2]");
		}
		echo $article->getArticle("CJO_VALUE[1]");
	}
	else {
		if (cjoProp::isBackend()) {
			print '<p class="error">'.$link.'. '.cjoI18N::translate('msg_article_offline').'</p>';
		}
	}
} elseif (cjoProp::isBackend()) {
	print '<p class="error">'.cjoI18N::translate('msg_no_alias_settings').'</p>';
}
?>