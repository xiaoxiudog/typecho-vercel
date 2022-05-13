<?php
require_once ("action/MultiUpload.php");
require_once("action/MetingAction.php");
require_once("action/LinksAction.php");
require_once ("libs/Tool.php");

/**
 * Helper::AddAction 的类必须是插件名_Action 的类名，自定义类名称无效，所以使用该类作为统一的分发请求
 */
class Handsome_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;
			
	public function insertLink()
	{
		if (Handsome_Plugin::form('insert')->validate()) {
			$this->response->goBack();
		}
		/** 取出数据 */
		$link = $this->request->from('name', 'url', 'sort', 'image', 'description', 'user');
		$link['order'] = $this->db->fetchObject($this->db->select(array('MAX(order)' => 'maxOrder'))->from($this->prefix.'links'))->maxOrder + 1;

		/** 插入数据 */
		$link['lid'] = $this->db->query($this->db->insert($this->prefix.'links')->rows($link));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('link-'.$link['lid']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('链接 <a href="%s">%s</a> 已经被增加',
		$link['url'], $link['name']), 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Handsome%2Fmanage-links.php', $this->options->adminUrl));
	}

	public function addHannysBlog()
	{
		/** 取出数据 */
		$link = array(
			'name' => "Hanny's Blog",
			'url' => "http://www.imhan.com",
			'description' => "寒泥 - Typecho插件开发者",
		);
		$link['order'] = $this->db->fetchObject($this->db->select(array('MAX(order)' => 'maxOrder'))->from($this->prefix.'links'))->maxOrder + 1;

		/** 插入数据 */
		$link['lid'] = $this->db->query($this->db->insert($this->prefix.'links')->rows($link));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('link-'.$link['lid']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('链接 <a href="%s">%s</a> 已经被增加',
		$link['url'], $link['name']), 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Handsome%2Fmanage-links.php', $this->options->adminUrl));
	}

	public function updateLink()
	{
		if (Handsome_Plugin::form('update')->validate()) {
			$this->response->goBack();
		}

		/** 取出数据 */
		$link = $this->request->from('lid', 'name', 'sort', 'image', 'url', 'description', 'user');

		/** 更新数据 */
		$this->db->query($this->db->update($this->prefix.'links')->rows($link)->where('lid = ?', $link['lid']));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('link-'.$link['lid']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('链接 <a href="%s">%s</a> 已经被更新',
		$link['url'], $link['name']), 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Handsome%2Fmanage-links.php', $this->options->adminUrl));
	}

    public function deleteLink()
    {
        $lids = $this->request->filter('int')->getArray('lid');
        $deleteCount = 0;
        if ($lids && is_array($lids)) {
            foreach ($lids as $lid) {
                if ($this->db->query($this->db->delete($this->prefix.'links')->where('lid = ?', $lid))) {
                    $deleteCount ++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('链接已经删除') : _t('没有链接被删除'),
        $deleteCount > 0 ? 'success' : 'notice');

        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Handsome%2Fmanage-links.php', $this->options->adminUrl));
    }

    public function sortLink()
    {
        $links = $this->request->filter('int')->getArray('lid');
        if ($links && is_array($links)) {
			foreach ($links as $sort => $lid) {
				$this->db->query($this->db->update($this->prefix.'links')->rows(array('order' => $sort + 1))->where('lid = ?', $lid));
			}
        }
    }


    public function uploadApi()
    {
        if ( class_exists("Handsome_Upload")) {
            $reflectionWidget =  new ReflectionClass("Handsome_Upload");
            if ($reflectionWidget->implementsInterface('Widget_Interface_Do')) {
                $this->widget("Handsome_Upload")->action();
            }
        }
    }

    public function metingApi()
    {
        if ( class_exists("Handsome_Meting_Action")) {
            $reflectionWidget =  new ReflectionClass("Handsome_Meting_Action");
            if ($reflectionWidget->implementsInterface('Widget_Interface_Do')) {
                $this->widget("Handsome_Meting_Action")->action();
            }
        }
    }


	public function action()
	{
        $action = $this->request->getPathInfo();
        if (strpos($action,"handsome-meting-api")){
            self::metingApi();
        }else if (strpos($action,"multi-upload")){
            self::uploadApi();
        } else if (strpos($action,"links-edit")){
            $this->db = Typecho_Db::get();
            $this->prefix = $this->db->getPrefix();
            $this->options = Typecho_Widget::widget('Widget_Options');

            if (Typecho_Widget::widget('Widget_User')->hasLogin()){//link相关的接口需要登录才可以访问
                $this->on($this->request->is('do=insert'))->insertLink();
                $this->on($this->request->is('do=addhanny'))->addHannysBlog();
                $this->on($this->request->is('do=update'))->updateLink();
                $this->on($this->request->is('do=delete'))->deleteLink();
                $this->on($this->request->is('do=sort'))->sortLink();
            }

        }



//		$this->response->redirect($this->options->adminUrl);
	}
}




