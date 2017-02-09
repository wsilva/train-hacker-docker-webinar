# Demo - Train'Hack Webinar


## Intro 

Esse repositório foi criado para apoiar as demostrações exibidas no 1º webinar Train'Hack. Mais informações em http://bit.ly/1-train-hacker


## Requisitos

 - Docker v1.12 - para conseguir montar nosso Swarm. 
 - Docker Compose 1.6.0 para trabalhar localmente

Para conseguirmos utilizar a funcionalidade ```docker stack deploy``` devemos utilizar o *Docker v1.13* e habilitar a opção experimental (ou superior), e o *Docker Compose v1.10* ou superior


## Aplicação rodando localmente

Para testar a aplicação localmente devemos seguir os passos:


### Mudando para seu username do DockerHub

Esse exemplo está com meu nome de usuário no Docker Hub, para acompanhar você deve alterar no arquivo *docker-compose.yml* nos serviços *web* e *consumer* conforme o exemplo:

    ...
    image: wfsilva/train-hacker-webinar-web:v1
    ...
    image: wfsilva/train-hacker-webinar-consumer:v1
    ...
    
para:

    ...
    image: seu-nome-de-usuario-no-docker-hub/train-hacker-webinar-web:v1
    ...
    image: seu-nome-de-usuario-no-docker-hub/train-hacker-webinar-consumer:v1
    ...
    
    
### Construindo as imagens

Devemos utilizando o Docker instalado localmente (Docker for Mac, Docker for Windows, Docker Toolbox ou Docker instalado no Linux)

Para construir as imagens do serviço *web* e do serviço *consumer* de uma só vez vamos até a pasta onde está o arquivo *docker-compose.yml* e utilizamos o comando:

	$ docker-compose build
	
	
### Enviando para o Docker Hub

Primeriramente utilize o seguinte comando para autenticar no Docker Hub pela linha de comando seguindo as instruções que serão solicitadas.

	$ docker login
	
Em seguida na mesma pasta utilizamos o comando abaixo para enviar todas as imagens de uma vez:

	$ docker-compose push

Vamos alterar a versão das imagens para v2 e no arquivo *demoapp/resources/views/pages/subscribe.blade.php* vamos alterar o header 1 n linh 9 da página de *V1* para *V2*.

	<h1>DEMOAPP (V1)</h1>

para 

	<h1>DEMOAPP (V2)</h1>
	
E de

	...
    image: seu-nome-de-usuario-no-docker-hub/train-hacker-webinar-web:v1
    ...
    image: seu-nome-de-usuario-no-docker-hub/train-hacker-webinar-consumer:v1
    ...
    
para

	...
    image: seu-nome-de-usuario-no-docker-hub/train-hacker-webinar-web:v2
    ...
    image: seu-nome-de-usuario-no-docker-hub/train-hacker-webinar-consumer:v2
    ...

Vamos regerar e reenviar as imagens ao Docker Hub:

	$ docker-compose build && docker-compose push

### Subindo a stack localmente

Para subir toda a stack de uma vez utilizamos o comando:

	$ docker-compose up -d
	
Onde o -d vai deixar os logs desanexados (detached) e o terminal livre para rodar comandos. Se quiser ver os logs use *docker-compose logs* para ver os útlimos logs ou utilize outros parâmetros para filtrar ou para acompanhar o log real time.


## Montando um cluster na Digital Ocean

Neste exemplo utilizei minha conta DigitalOcean, quem quiser pode utilizar sua própria conta, para isso é necessário utilizar o seu token nos exemplos a seguir. 

Se não possuír conta na Digital Ocean e quiser testar, basta cadastrar pelo link https://m.do.co/c/8fcb11eb2657 você consegue dez dólares de crédito.

Se preferir pode criar de maneira similar usando Virtualbox ou VMWare para simular um cluster localmente. (exige recursos de máquina)

Guardando meu Token numa variável de ambiente:

	$ DOTOKEN=$(cat ~/Dropbox/digital-ocean/DO_TOKEN.txt)
	
Desligue o seu Docker local com o comando *sudo service docker stop* se estiver no Linux, *osascript -e 'quit app "Docker"'* se estiver no OSX. No Windows não lembro como.


### Docker Machine para criar as VMs

Crie suas VMs rodando Docker na Digital Ocean. Neste exemplo estou utilizando o Docker machine para isso. Também atente para a flag *--engine-opt 'experimental=true'* para rodarmos a versão experimental em nossas VMS, util para trabalharmos com o comando *stack*:

    $ for i in {1..4}; do docker-machine create --driver digitalocean --digitalocean-access-token=$DOTOKEN --engine-opt 'experimental=true' host$i; done;
    
De maneira alternativa você pode fazer com VMs usando vmwarefusion ou virtualbox:

    $ for i in {1..4}; do docker-machine create --driver vmwarefusion  --engine-opt 'experimental=true' host$i; done;
    $ for i in {1..4}; do docker-machine create --driver virtualbox  --engine-opt 'experimental=true' host$i; done;
    
Utilizaremos o visualizador criado pelo Mano Marks do Docker:

    $ docker-machine ssh host1 \
        docker run -it -d \
        -p 8080:8080 \
        -e HOST=$(docker-machine ip host1) \
        -v /var/run/docker.sock:/var/run/docker.sock \
        manomarks/visualizer
        
Para visualizar usamos o IP do host onde criamos. Se estiver no OSX como eu:
 
    $ open -a Firefox "http://$(docker-machine ip host1):8080"
    

### Iniciando o Swarm mode

Iniciando o Swarm usando swarmkit (docker 1.12+):

    $ docker-machine ssh host1 docker swarm init --advertise-addr $(docker-machine ip host1)
    
Pegando o Token para adicionarmos mais máquinas ao cluster:

    $ SWMTOKEN=`docker-machine ssh host1 docker swarm join-token -q worker`
    
Juntando as demais máquinas ao cluster:

    $ for i in {2..4}; do docker-machine ssh host$i docker swarm join --token $SWMTOKEN $(docker-machine ip host1):2377; done
    
Apontando meu docker client para o daemon rodando no swarm:

    $ eval "$(docker-machine env host1)"


### Subindo um serviço

Para subirmos um srerviço temos que criar uma rede do tipo overlay para que os serviços se comuniquem por ela:

    $ docker network create --driver overlay nginx
    
Em seguida criamos o serviço

	$ docker service create --name nginx -p 80:80 nginx
	
Mesmo com apenas um container rodando conseguimos acessar pelo ip de qualque uma das VMs graças a rede olverlay.

    $ open -a Firefox "http://$(docker-machine ip host1)"
    $ open -a Firefox "http://$(docker-machine ip host2)"
    $ open -a Firefox "http://$(docker-machine ip host3)"
    $ open -a Firefox "http://$(docker-machine ip host4)"
  
Vamos remover este serviço por enquanto:

	$ docker service rm nginx


### Deploy da Stack

Vamos subir toda a stack de uma vez usando nosso arquivo *docker-compose.yml*:

	$  docker stack deploy --compose-file docker-compose.yml
	
Podemos monitorar a linha de comando:

	$ docker stack ls
	$ docker stack services demoapp
	$ docker stack ps demoapp

Ou para cada serviço

	$ docker service ls
	$ docker service ps demoapp_web
	
Podemos escalar horizontalmente um serviço de 2 maneiras:

	$ docker service scale demoapp_consumer=3
	$ docker service update --replicas=3 demoapp_web
	
Podemos mudar parâmetros ou até a imagem de um serviço inclusive com rolling update


Atualizando limitação de recursos

	$ docker service update demoapp_consumer \
	  --limit-memory 256mb 

Atualizando a versão da imagem:

	$ docker service update demoapp_web \
	  --image wfsilva/train-hacker-webinar-web:v2 \
	  --update-parallelism 2 \
	  --update-delay=5s
	  
Como passamos no exemplo anterior passamos as diretivas de deploy então elas sobreescrevem as que estavam definidas no *docker-compose.yml*.


### Gerencia dos Nós (HA)

Para listarmos os nós (VMs) disponíveis

	$ docker node ls

Se precisarmos separar um nó devemos drenar seus contêineres. Assim eles serão provisionados em outros nós disponiveis:

	$ docker node update --availability drain host4

Para voltar o nó ao cluster:

	$ docker node update --availability active host2
	
Podemos transformar um nó worker em um nó manager:

	$ docker node promote host2
	$ docker node promote host3
	$ docker node promote host4

Ou despromover

	$ docker node demote host4

Para ver o RAFT Consensus em ação elegendo um novo manager principal vamos derrubar nosso manager líder atual.

Mas para isso vamos primeiro conectar com um nó manager que não seja o líder:

	eval "$(docker-machine env host2)"

Em seguida monitoramos os nós

	watch -n 1 docker node ls

E em outro terminal derrubamos o antigo líder:

	docker-machine rm host1

Em instantes teremos um dos 2 nós managers restantes como um novo líder.


## Limpando a casa

Se estiver fazendo estudos o ideal é desligar suas VMs se elas estiverem em algumm cloud provider, se você esquecer as máquinas ligadas vai ter uma surpresa não tão grata em seu cartão de crédito.


