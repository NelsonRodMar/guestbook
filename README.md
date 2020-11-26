# Guestbook

A project following the book [Symfony 5: The Fast Track](https://symfony.com/book) write by Fabien Potencier

## Symfony local server
Start server<br/>
``
make start
``

Stop server<br/>
``
make stop
``

Run test<br/>
``
make tests
``

Open in browser<br/>
``
symfony open:local
``


## Entity Workflow

Generate workflow ([Graphviz](https://www.graphviz.org/) required) <br/>
``
symfony console workflow:dump 'entity' | dot -Tpng -o workflow.png
``

Comment workflow<br/>
![alt text](./doc/workflow_comment.png "Comment Workflow")


## Other information

* Commit messages based on [Gitmoji](https://gitmoji.carloscuesta.me/)