# Simple-php-daemon

Repository for ALL, My open source codes for Anyone!

PT-BR
Este é um codigo simples de Daemon.
Foi desenvolvido para CENTOS e PHP 5.0 ou maior

Para configurar este código você precisa, modificar os paths e usuário dentro do main.xml. alem de dar permissão de execução para o script daemon.php no diretório bin.
chmod +x daemon.php

Dentro do arquivo CONF.class.php no diretório lib, certifique-se de que a variavel private $xml, está com o PATH correto
exemplo:
private $xml = "/app/daemon/lib/main.xml";

Após checar o disposto acima, basta executar:

./daemon.php --help

Utilze os arquivos dentro do diretorio ETC como base para criar os seus próprios arquivos de configuração, eles trabalham em conjunto com o arquivo Example.class.php dentro do diretório LIB.

Em caso de dúvidas , me mande um e-mail.

EN-US

This is a simple Daemon code.
It was developed for CENTOS and PHP 5.0 or Higher

To configure this code you need to modify the paths and user inside main.xml. in addition to giving execution permission to the daemon.php script in the bin directory.
chmod +x daemon.php

Inside the CONF.class.php file in the lib directory, make sure that the private $xml variable has the correct PATH
example:
private $xml = "/app/daemon/lib/main.xml";

After checking the above, just run:

./daemon.php --help

Use the files inside the etc directory as a base to create your own configuration files, they work together with the Example.class.php file inside the lib directory.

If in doubt, send me an email.
