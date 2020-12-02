# Guestbook

A project following the book [Symfony 5: The Fast Track](https://symfony.com/book) write by Fabien Potencier

## Symfony local server
Start the web application and the SPA*<br/>
``
make start
``

Install the web application and the SPA*<br/>
``
make install
``

Stop the web application and the SPA*<br/>
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
* *The [Symfony CLI](https://symfony.com/download) is required to run the make command