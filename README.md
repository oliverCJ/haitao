#海淘系统
###1.NGINX配置:
<pre>
server {
        listen 8010;
        root /path/haitao/kernel/Server/Public;
        server_name haitao.com 127.0.0.1;

        location / {
                root $filePath;
                index index.php;
                try_files $uri /index.php?_r_=$uri&$query_string;
        }

        location ~ \.php$ {
                root $filePath;
                fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                include fastcgi_params;
        }
}

</pre>

###2.调用说明
使用http协议

TODO 内部将封装调用方法

测试调用:
<pre>
http:://127.0.0.1:8010/apptest/Test/getSomeData?firstarg=first&secondarg=second
系统内部将自动解析
应用:apptest
接口类:Test
接口方法:getSomeData
参数:array(
    'firstarg' => first,
    'secondarg' => senond
)
</pre>
返回值会封装成json格式
