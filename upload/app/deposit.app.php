 <?php
/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class DepositApp extends MemberbaseApp
{
    /**
     *    预存款
     *
     *    @author    jjc
     *    @return    void
     */
    function deposit()
    {

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
                         LANG::get('deposit')
                         );

        /* 当前所处子菜单 */
        $this->_curmenu('deposit');
        /* 当前用户中心菜单 */
        $this->_curitem('my_deposit');
       
    
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . '预存款');
        $this->display('deposit.index.html');
    }
         /**
     *    三级菜单
     *
     *    @author    jjc
     *    @return    void
     */
    function _get_member_submenu()
    {
        $submenus =  array(
            array(
                'name'  => 'deposit',
                'url'   => 'index.php?app=deposit&amp;act=deposit',
            ),
            array(
                'name'  => 'account_setting',
                'url'   => 'index.php?app=deposit&amp;act=account_setting',
            ),
            array(
                'name'  => 'transaction_history',
                'url'   => 'index.php?app=deposit&amp;act=transaction_history',
            ),
             array(
                'name'  => 'financial_details',
                'url'   => 'index.php?app=deposit&amp;act=financial_details',
            ),
        );
        if ($this->_feed_enabled)
        {
            $submenus[] = array(
                'name'  => 'feed_settings',
                'url'   => 'index.php?app=member&amp;act=feed_settings',
            );
        }

        return $submenus;
    }
}