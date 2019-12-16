# A blog app based on Slim, Eloquent ORM and Twig
#### PHP Team Treehouse TechDegree project #5

| [What the app does](#what-the-app-does) | [Installation instructions](#installation-instructions) | [Tech used](#tech-used) | [Folder & file structure](#folder--file-structure) |

## What the app does
#### In this blog app you will experience the following functionalities:
1. Viewing all blog posts, incl. their tags

![Image of Main Screen](screenshots/posts.png)

2. Filtering the blog posts by tag

![Image of Main Screen filtered by tag](screenshots/filtered_by_tag.png)

3. Viewing blog details, reading its comments and adding new comments

![Image #1 of post details screen](screenshots/blog_detail_1.png)

![Image #2 of post details screen](screenshots/blog_detail_2.png)

4. Adding blogs, incl. their tags

![Image of Add Post Screen](screenshots/add_post.png)

5. Editing blog details, incl. their tags

![Image of Edit Post Screen](screenshots/edit_post.png)

6. Deleting blog posts

![Image of Delete Post Screen](screenshots/delete_post.png)

7. Managing tags: retrieving, adding, updating & deleting tags. And when done, of course, you can start using the tags for your posts

![Image of Tags Screen](screenshots/tags.png)

## Installation instructions
#### After downloading this project, make sure you run the following composer command in the project folder to install the right packages:
```bash
composer update
```

## Tech used
#### In this Blog the following main concepts, languages, frameworks, packages and other technologies are applied:
PHP | MVC pattern | OOP | Slim | Eloquent ORM (Laravel) | SQLite | Twig | Slim CSRF | Monolog | HTML | CSS

## Folder & file structure
#### The most important folders & files within this project:

      .
      ├── log                         # contains all log details  
      │   └── app.log                 
      ├── public                      # contains css files, images, htaccess and index.php files  
      │   ├── css
      │   └── img
      ├── src                         # contains the database file & the primary Slim files  
      │   ├── Classes                 # contains the Post, Comment & Tag class files, based on Eloquent ORM  
      │   └── routes                  # contains the post, comment & tag route files  
      └── templates                   # contains all twig templates
