# Search

Search plugin that indexes content from various models and makes that content searchable.

Indexed content is saved in a 'search_documents' table. The Search plugin will query this table which is
a lot more efficient than querying every searchable model/table separately.

Each search query is saved in the 'search_queries' table. If you want you can create a SearchQueryAdmin model
and include it in the admin of the website if eg. the client wants to view what search queries the visitors
of their website are doing.

## Requirements

CakePHP 2.x  
System plugin   
Menu plugin

## Installation

	Console/cake system.plugin add Search

## Configs

The plugin has its own routes (for nl, fr and en) to a default searchform. If you want to use them add them to the `CakePlugin::load` in `app/Config/bootstrap.php`

	CakePlugin::load('Search', array('routes' => true));


	
## Libraries

## Behaviors

### SearchableBehavior

Include the Searchable behavior in the model(s) you want to make "searchable". In normal circumstances you will add the Searchable behavior to a TranslationModel.

    public $actsAs = array('Search.Searchable' => array(
        'order' => 10,
        'fields' => array('title' => 10, 'body' => 20 ...),
        'publishable' => true
    ));

* The 'order' value can be used to list searchresults for certain models before results from other models if eg.
  a certain model has absolute priority over another model. You can assign identical order values (or don't
  add this setting) if all models should be treated equally.
  
* The 'fields' array must contain field/score pairs of the fields you want to index. With the score value you can
  indicate that a match on that field is more important than a match on another field with a lower score.
  The scores will be used to order the searchresults (after the optional order value from above).

* If you set the 'publishable' setting as true, the Searchable behavior will take into account the publishable
  property of the model (eg. only index the contents if online = true).
  
## Components

## Helpers

### SearchHelper

Contains methods used by the Search/index view (result, hightlight, excerpt, ...). In normal circumstances
you won't need to use these methods directly and just use the Search/index view by including the routing config
from the Search plugin (combined with the searchbar element if necessary).

## Elements

### searchbar

Element you can use to include a search form anywhere you want in your views.

    echo $this->element('Search.searchbar');

### searchresult

Element used by the SearchHelper to display a search result. You won't use this element directly in normal
circumstances.

## Shells

### SearchShell

You can use this shell to:
 
* List all searchable models
* Build a search index (if you need to initialize a search index from existing content)