Readme.txt

// 修改文件以及目录
1.hybridauth-2.1.2  该目录新增文件
examples 目录
  qq/index.php    新增qq目录以及index.php文件，用于qq测试
  sina/index.php  新增sina目录以及index.php文件，用于sina测试
  index.html      修改主要是新增界面可以显示sina和qq的HTML语句

hybridauth 目录
  ./Hybrid
  ./../Providers
  ./../../                  该目录下新增QQ.php与Sina.php也是核心文件
  
  ./../resources
  ../../../config.php.tpl   修改该文件，新增qq与sina的配置

  ./../thirdparty
  ./../OAuth
  ./../../OAuth2Client.php  修改该文件中的两个方法authorizeUrl()与authenticate()
  ./../../                  该目录下新增Sina目录以及saetv2.ex.class.php  该目录可以直接拷贝

3 hybridauth/Hybrid/
  ./../Provider_Model.php    修改该文件的logout()方法

4 修改hybridauth/install.php 文件 从界面上设定qq与sina的id与secret.

执行方法：
  1.首先安装hybridauth-2.1.2
    执行install.php文件，然后进入examples目录，测试QQ与sina

  2.参考QQ与Sina的OAuth2协议以及api
   qq: 
    参考腾讯微博开放平台:  http://dev.t.qq.com/
    参考api  :  https://open.t.qq.com/api/user/info 
   sina:      
    参考sina开放平台地址： http://open.weibo.com/
    参考sina api : https://api.weibo.com/2/users/show.json

  3.说明id以及secret
    QQ的id以及secret是通过http://dev.t.qq.com/development创建的网页应用
    Sina的id以及secret是通过http://open.weibo.com/apps/new?sort=web创建的网站应用

  4. 测试访问地址：
   http://infocusphone.com/brid/hybridauth-2.1.2/examples/  


