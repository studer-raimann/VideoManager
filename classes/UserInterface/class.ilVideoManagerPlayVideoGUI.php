<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerVideo.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerFolder.php');
require_once("./Services/Rating/classes/class.ilRatingGUI.php");
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Count/class.vidmCount.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/Subscription/class.vidmSubscriptionButtonGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerQueryBuilder.php');

/**
 * Class ilVideoManagerPlayVideoGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      ilVideoManagerPlayVideoGUI: ilRatingGUI
 * @ilCtrl_IsCalledBy ilVideoManagerPlayVideoGUI: ilRouterGUI
 *
 */
class ilVideoManagerPlayVideoGUI {

	/**
	 * @var
	 */
	protected $parent_gui;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilVideoManagerPlugin
	 */
	protected $pl;
	/**
	 * @var ilVideoManagerVideo
	 */
	protected $video;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var array
	 */
	protected $options;
	/**
	 * @var ilObjUser
	 */
	protected $usr;


	/**
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->usr = $DIC->user();
		$this->parent_gui = $parent_gui;
		$this->pl = ilVideoManagerPlugin::getInstance();
		//		$this->tpl = $DIC->ui()->mainTemplate();
		$this->tpl = new ilTemplate('tpl.video_player.html', false, false, $this->pl->getDirectory());
		$this->video = new ilVideoManagerVideo($_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
		vidmCount::up($this->video->getId(), $this->usr->getId());
	}


	public function init() {
		if (! ilVideoManagerObject::__checkConverting($this->video->getId())) {
			ilUtil::sendInfo($this->pl->txt('msg_vid_converting'), true);
		}
		$this->initMediaPlayer();
		$this->initDescription();
		global $DIC;
		$tpl = $DIC->ui()->mainTemplate();
		$this->tpl->setVariable('RELATED_VIDEOS_TABLE', $this->getRelatedVideosTableHTML());
		$tpl->setContent($this->tpl->get());
		$tpl->setTitle('Play Video');
	}


	protected function initMediaPlayer() {
//		require_once('./Services/MediaObjects/classes/class.ilPlayerUtil.php');
//		ilPlayerUtil::initMediaElementJs();
		$this->tpl->setVariable('POSTER_SRC', $this->video->getPosterHttp());
		$this->tpl->setVariable('VIDEO_SRC', $this->video->getHttpPath() . '/' . $this->video->getTitle());
	}


	/**
	 * @return string
	 */
	protected function getRelatedVideosTableHTML() {
		$options = array(
			'cmd'    => 'related_videos',
			'search' => array(
				'method' => 'related',
			),
			'limit'  => 4,
		);

		require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/UserInterface/class.ilVideoManagerVideoTableGUI.php');
		$ilVideoManagerVideoTableGUI = new ilVideoManagerVideoTableGUI($this, $options, $this->video);
//		return $ilVideoManagerVideoTableGUI->getHTML();

		// implemented but not used new version of gui
		$ilVideoManagerQueryBuilder = new ilVideoManagerQueryBuilder($options, $this->video);

		$xvidListGUI = new xvidListGUI($ilVideoManagerQueryBuilder->getVideos());
		$xvidListGUI->setSize(xvidListGUI::SIZE_TINY);

		return $xvidListGUI->render();
	}


	protected function initDescription() {
		$this->initRating();

		$this->tpl->setVariable('TITLE', $this->video->getTitle());

		if ($this->video->getDescription() && strlen($this->video->getDescription()) > 350) {
			$this->tpl->setVariable('DESCRIPTION', $this->video->getDescription());
			$this->tpl->setVariable('DESCRIPTION_SHORT', $this->video->getDescription(350));
			$this->tpl->setVariable('MORE', '[' . $this->pl->txt('common_more') . ']');
			$this->tpl->setVariable('LESS', '[' . $this->pl->txt('common_less') . ']');
		} elseif ($this->video->getDescription()) {
			$this->tpl->setVariable('DESCRIPTION_SHORT', $this->video->getDescription());
		}

		if ($tags = $this->video->getTags()) {
			$this->tpl->setVariable('TAGS_KEY', $this->pl->txt('player_tags_key'));
			foreach ($this->video->getTags() as $tag) {
				$this->tpl->setCurrentBlock('tags');
				$this->ctrl->setParameterByClass(ilVideoManagerUserGUI::class, 'search_value', $tag);
				$this->ctrl->setParameterByClass(ilVideoManagerUserGUI::class, 'search_method', 'tag');
				$this->tpl->setVariable('TAG_SEARCH', $this->ctrl->getLinkTargetByClass(ilVideoManagerUserGUI::class, ilVideoManagerUserGUI::CMD_SEARCH));
				$this->tpl->setVariable('TAGS_VALUE', $tag);
				$this->tpl->parseCurrentBlock();
			}
		}

		$tree = new ilVideoManagerTree(1);
		$category = new ilVideoManagerFolder($tree->getParentId($this->video->getId()));
		$this->tpl->setVariable('CATEGORY_KEY', $this->pl->txt('player_category_key'));
		$this->tpl->setVariable('CATEGORY_VALUE', $category->getTitle());

		if (vidmConfig::getV(vidmConfig::F_ACTIVATE_SUBSCRIPTION)) {
			$sub = new vidmSubscriptionButtonGUI();
			$sub->setSize(vidmSubscriptionButtonGUI::SIZE_SMALL);
			$this->ctrl->setParameter($this->parent_gui, ilVideoManagerAdminGUI::PARAM_NODE_ID, $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
			$sub->generate($category, ilVideoManagerUserGUI::CMD_PLAY_VIDEO);
			$this->tpl->setVariable('SUBSCRIPTION_BUTTON', $sub->getHTML($category));
		}

		if (vidmCount::isActive()) {
			$this->tpl->setVariable('VIEWS_KEY', $this->pl->txt('player_views_key'));
			$this->tpl->setVariable('VIEWS', vidmCount::countV($this->video->getId()));
		}

		$this->ctrl->setParameterByClass(ilVideoManagerUserGUI::class, 'search_value', $category->getId());
		$this->ctrl->setParameterByClass(ilVideoManagerUserGUI::class, 'search_method', 'category');
		$this->tpl->setVariable('CATEGORY_SEARCH', $this->ctrl->getLinkTargetByClass(ilVideoManagerUserGUI::class, ilVideoManagerUserGUI::CMD_SEARCH));
	}


	protected function initRating() {
		$this->ctrl->setParameterByClass(ilRatingGUI::class, ilVideoManagerAdminGUI::PARAM_NODE_ID, $_GET[ilVideoManagerAdminGUI::PARAM_NODE_ID]);
		$rating = new ilRatingGUI();
		$rating->setObject($this->video->getId(), ilVideoManagerObject::TYPE_VID);
		$this->tpl->setVariable('RATING', $rating->getHTML());
	}


	protected function subscribe() {
		$subscription = new vidmSubscription();
		$subscription->setUsrId($this->usr->getId());
		$subscription->setCatId($_GET[ilVideoManagerUserGUI::SUB_CAT_ID]);
		$subscription->create();

		ilUtil::sendSuccess($this->pl->txt('msg_subscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
		$this->ctrl->redirect($this, ilVideoManagerUserGUI::CMD_PERFORM_SEARCH);
	}


	protected function unsubscribe() {
		$cat_id = $_GET[ilVideoManagerUserGUI::SUB_CAT_ID];
		$user_id = $this->usr->getId();
		foreach (vidmSubscription::where(array( 'usr_id' => $user_id, 'cat_id' => $cat_id ))->get() as $subscription) {
			$subscription->delete();
		}

		ilUtil::sendSuccess($this->pl->txt('msg_unsubscribed_successfully'), true);
		$this->ctrl->saveParameter($this, 'video_tbl_table_nav');
		$this->ctrl->redirect($this, ilVideoManagerUserGUI::CMD_PERFORM_SEARCH);
	}
} 