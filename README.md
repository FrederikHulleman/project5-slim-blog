# PHP Team Treehouse TechDegree project #5
## A blog app based on Slim, Eloquent ORM and Twig

#### After downloading this project, make sure you run the following composer command in the project folder to install the right packages:
```bash
composer update
```

### In this Blog the following main concepts, languages, frameworks, packages and other technologies are applied:
PHP | MVC pattern | OOP | Slim | Eloquent ORM (Laravel) | SQLite | Twig | Slim CSRF | Monolog | HTML | CSS

## In this blog app you will experience the following functionalities:
#### (see screenshots below)
1. Viewing all blog posts, incl. their tags
2. Filtering the blog posts by tag
3. Viewing blog details, reading its comments and adding new comments
4. Adding blogs, incl. their tags
5. Editing blog details, incl. their tags
6. Deleting blog posts
7. Managing tags: retrieving, adding, updating & deleting tags. And when done, of course, you can start using the tags for your posts

## The most important folders & files within this project:

|--log   
|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|-- app.log&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# contains all log details  
|--public&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# contains css files, images, htaccess and index.php files  
|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|--css  
|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|--img  
|--src&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# contains the database file & the primary Slim files   
|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|--Classes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# contains the Post, Comment & Tag class files, based on Eloquent ORM  
|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|--routes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# contains the post, comment & tag route files  
|--templates&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# contains all twig templates  

      .
      ├── build                   # Compiled files (alternatively `dist`)
      ├── docs                    # Documentation files (alternatively `doc`)
      ├── src                     # Source files (alternatively `lib` or `app`)
      ├── test                    # Automated tests (alternatively `spec` or `tests`)
      ├── tools                   # Tools and utilities
      ├── LICENSE
      └── README.md

## Screenshots
1. Viewing all blog posts, incl. their tags

![Image of Main Screen](screenshots/posts.png)

2. Filtering the blog posts by tag

![Image of Main Screen](screenshots/posts.png)

3. Viewing blog details, reading its comments and adding new comments

![Image of Main Screen](screenshots/posts.png)

4. Adding blogs, incl. their tags

![Image of Main Screen](screenshots/posts.png)

5. Editing blog details, incl. their tags

![Image of Main Screen](screenshots/posts.png)

6. Deleting blog posts

![Image of Main Screen](screenshots/posts.png)

7. Managing tags: retrieving, adding, updating & deleting tags. And when done, of course, you can start using the tags for your posts

![Image of Main Screen](screenshots/posts.png)

Folder Structure Conventions
============================

> Folder structure options and naming conventions for software projects

### A typical top-level directory layout

    .
    ├── build                   # Compiled files (alternatively `dist`)
    ├── docs                    # Documentation files (alternatively `doc`)
    ├── src                     # Source files (alternatively `lib` or `app`)
    ├── test                    # Automated tests (alternatively `spec` or `tests`)
    ├── tools                   # Tools and utilities
    ├── LICENSE
    └── README.md
