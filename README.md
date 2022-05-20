# Typecho博客_Vercel PHP环境

修改vercel.json自定义php版本
- `vercel-php@0.5.1` - PHP 8.1.x
- `vercel-php@0.4.0` - PHP 8.0.x  #默认
- `vercel-php@0.3.2` - PHP 7.4.x

[![Deploy to Vercel](https://vercel.com/button)](https://vercel.com/import/project?template=https://github.com/pbloods/typecho/)

配置数据库

通过 vercel 测试域名访问 typecho 后台，填写数据库信息，注意 vercel 无法直接修改文件，[typecho 后台](https://typecho-phi-ten.vercel.app/)会根据你填写的数据生成配置文件内容，手动在本地项目根目录下新建文件`config.inc.php`，填入生成的配置文件内容，重新上传即可配置成功。
