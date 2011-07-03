<?php

# Abstract Class: activeRecord
# ============================
#
# Active Record objects don't specify their attributes directly, but rather infer them from the
# table definition with which they're linked. Adding, removing, and changing attributes and their
# type is done directly in the database.
#
# Any change is instantly reflected in the Active Record objects. The mapping that binds a given
# Active Record class to a certain database table will happen automatically in most common cases,
# but can be overwritten for the uncommon ones.
#
#
#
# Creation
# --------
#
# Active Records accept constructor parameters in an array. The array method is especially
# useful when you're receiving the data from somewhere else, like an HTTP request.
#
# It works like this:
#
#	$user = new User(array('name' => 'David', 'occupation' => 'Code Artist'));
#	user->name => "David"
#
# And of course you can just create a bare object and specify the attributes after the fact:
#
#	$user = new User;
#	$user->name = "David";
#	$user->occupation = "Code Artist";
#
#
#
# Conditions
# ----------
#
# Conditions can either be specified as a string or array representing the WHERE-part of an
# SQL statement.
#
# The array form is to be used when the condition input is tainted and requires sanitization.
# The string form can be used for statements that don't involve tainted data.
#
#	class User extends activeRecord {
#		function authenticate_unsafely($user_name, $password) {
#			return $this->find('first', array('conditions' => 'user_name = "' . $user_name . '" AND password = "' . $password . '"'));
#		}
#
#		function authenticate_safely($user_name, $password) {
#			return $this->find('first', array('conditions' => array('user_name = ? AND password = ?', $user_name, $password)));
#		}
#
#		function authenticate_safely_simply($user_name, $password) {
#			return $this->find('first', array('conditions' => array('user_name' => $user_name, 'password' => $password)));
#		}
#	}
#
# The authenticate_unsafely method inserts the parameters directly into the query and is thus
# susceptible to SQL-injection attacks if the user_name and password parameters come directly
# from an HTTP request. The authenticate_safely  and authenticate_safely_simply both will sanitize
# the user_name and password before inserting them in the query, which will ensure that an attacker
# can't escape the query and fake the login (or worse).
#
#
#
# Overwriting default accessors
# -----------------------------
#
# All column values are automatically available through basic accessors on the Active Record
# object, but sometimes you want to specialize this behavior. This can be done by overwriting the
# default accessors (using the same name as the attribute) and calling read_attribute(attr_name)
# and write_attribute(attr_name, value) to actually change things.
#
#	class Song extends activeRecord {
#		function set($minutes) {
#			$this->write_attribute('length', $minutes * 60);
#		}
#
#		function get() {
#			$this->read_attribute('length') / 60;
#		}
#	}
#
# You can alternatively use set_length and get_length instead of write_attribute($attribute, $value)
# and read_attribute($attribute) as a shorter form.
#
#
#
# Accessing attributes before they have been typecasted
# -----------------------------------------------------
#
# Sometimes you want to be able to read the raw attribute data without having the column-determined
# typecast run its course first.
#
# That can be done by using the <attribute>_before_type_cast accessors that all attributes have.
#
# For example, if your Account model has a balance attribute, you can call $account->balance_before_type_cast
# or $account->id_before_type_cast.
#
# This is especially useful in validation situations where the user might supply a string for an
# integer field and you want to display the original string back in an error message. Accessing the attribute
# normally would typecast the string to 0, which isn't what you want.
#
#
#
# Dynamic attribute-based finders
# -------------------------------
#
# Dynamic attribute-based finders are a cleaner way of getting (and/or creating) objects by simple
# queries without turning to SQL. They work by appending the name of an attribute to find_by_ or find_all_by_,
# so you get finders like $Person->find_by_user_name, $Person->find_all_by_last_name, $Payment->find_by_transaction_id.
#
# So instead of writing $Person->find('first', array('conditions' => array('user_name' => $user_name))),
# you just do $Person->find_by_user_name($user_name).
# And instead of writing $Person->find('all', array('conditions' => array('last_name' => $last_name))),
# you just do $Person->find_all_by_last_name($last_name).
#
# It's also possible to use multiple attributes in the same find by separating them with "_and_",
# so you get finders like $Person->find_by_user_name_and_password or even $Payment->find_by_purchaser_and_state_and_country.
# So instead of writing $Person->find('first', array('conditions' => array('user_name' => $user_name, 'password' => $password))),
# you just do $Person->find_by_user_name_and_password($user_name, $password).
#
# It's even possible to use all the additional parameters to find. For example, the full interface for
# $Payment->find_all_by_amount is actually $Payment->find_all_by_amount($amount, $options).
# And the full interface to $Person->find_by_user_name is actually $Person->find_by_user_name($user_name, $options).
# So you could call $Payment->find_all_by_amount(50, array('order' => 'created_on')).
#
# The same dynamic finder style can be used to create the object if it doesn't already exist.
# This dynamic finder is called with find_or_create_by_ and will return the object if it already exists
# and otherwise creates it, then returns it.
#
# Use the find_or_initialize_by_ finder if you want to return a new record without saving it first.
#
# Saving arrays and other non-mappable objects in text columns
# Active Record can serialize any object in text columns using serialize.
# This makes it possible to store arrays and other non-mappable objects without doing any additional work.
#
#	class User extends ActiveRecord {
#		$_serialize = 'preferences';
#	}
#
#	$User = new User;
#	$user = $User->create(array('preferences' => array("background" => "black", "display" => "large")));
#	$user->find($user->id)->preferences => array("background" => "black", "display" => "large");
#
#
#
# Connection to multiple databases in different models
# ----------------------------------------------------
#
# All ActiveRecord objects has a class property named _database.
# Setting this property to a value, makes object establish connection with the given database configuration.
#
# This feature is implemented by keeping a connection pool in DatabaseManager that is an array indexed
# by database configuration.
#
# If a connection is requested, the query will be made with this connection configuration.
abstract class ActiveRecord {

	# _database
	# ===================
	#
	# Sets the database configuration witch the model will be relationed. If no database is given,
	# the connection for the model will be made with the default database configuration.
	#
	# Always take note that the configuration name must prepend the environment.
	# On config/database.php file:
	#
	#	$_database_environments = array(
	#		'development' => array(
	#			'adapter' => 'mysqli',
	#			'database' =>  'site',
	#			'username' => 'root',
	#			'password' => 'some_password_i_cant_remember',
	#			'host' => 'localhost',
	#			'port' => '3306',
	#			'socket' => '',
	#			'encoding' => 'utf8'
	#		),
	#		'test' => array(
	#			'adapter' => 'mysqli',
	#			'database' => 'site',
	#			'username' => 'root',
	#			'password' => 'some_password_i_cant_remember',
	#			'host' => 'localhost',
	#			'port' => '3306',
	#			'socket' => '',
	#			'encoding' => 'utf8'
	#		),
	#		'production' => array(
	#			'adapter' => 'mysqli',
	#			'database' => 'site',
	#			'username' => 'root',
	#			'password' => 'some_password_i_cant_remember',
	#			'host' => 'localhost',
	#			'port' => '3306',
	#			'socket' => '',
	#			'encoding' => 'utf8'
	#		),
	#		'duo_development' => array(
	#			'adapter' => 'mysqli',
	#			'database' =>  'site',
	#			'username' => 'root',
	#			'password' => 'some_password_i_cant_remember',
	#			'host' => 'localhost',
	#			'port' => '3306',
	#			'socket' => '',
	#			'encoding' => 'utf8'
	#		),
	#		'duo_test' => array(
	#			'adapter' => 'mysqli',
	#			'database' => 'site',
	#			'username' => 'root',
	#			'password' => 'some_password_i_cant_remember',
	#			'host' => 'localhost',
	#			'port' => '3306',
	#			'socket' => '',
	#			'encoding' => 'utf8'
	#		),
	#		'duo_production' => array(
	#			'adapter' => 'mysqli',
	#			'database' => 'site',
	#			'username' => 'root',
	#			'password' => 'some_password_i_cant_remember',
	#			'host' => 'localhost',
	#			'port' => '3306',
	#			'socket' => '',
	#			'encoding' => 'utf8'
	#		)
	#	);
	#
	# Some model classes:
	#
	#	class User extends activeRecord {
	#		function connecting_on_default_database($user_name, $password) {
	#
	#		}
	#	}
	#
	#	class Administrator extends activeRecord {
	#		protected $_database = 'duo';
	#		function connecting_on_duo_database($user_name, $password) {
	#
	#		}
	#	}
	#
	# On these examples you can view the difference between connecting to the default database and
	# explicitely assigning the database to work with.
	#
	# I strongly recommend to work with only one database. Life is simpler with one database!
	# Aim for the single database approach unless you know you need multiple databases.
	protected $_database = '';

	# USED INTERNALLY
	# _connection
	# =====================
	#
	# When a object is created, it's connected to the specified database. This property stores the
	# connection to use it on every query.
	protected $_connection = false;

	# _primary_key
	# ======================
	#
	# If your database table use different name for its primary key column you can set this property
	# to define whatever column you want.
	#
	# For a complete automatic management of the model, the primary key must be an autonumeric integer
	# on the database.
	# This isn't always the case, so setting this property and using the callback before_save, you can work
	# with more complex models and tables without autonumerics.
	#
	# The simplest case is the id autonumeric field. In this case a Tag::find(5) will be executed on database
	# as 'select * from tags where id = 5'.
	#
	# This is the recommended way to work with Comodo Framework.
	#
	# On a table without an autonumeric we can afford the task doing the next:
	#
	#	Class Tag extends activeRecord {
	#		protected $_primary_key = 'no_autonumeric_field';
	#
	#		protected function before_save() {
	#			$max = $this->max('no_autonumeric_field');
	#			$this->no_autonumeric_field = $max + 1;
	#		}
	#	}
	#
	# In this example you can see how we can search for the max field value and increment this to get a
	# pseudo autonumeric.
	protected $_primary_key = 'id';

	# _table_name
	# =====================
	#
	# As a convention, the table used to store/retrieve the model data is made by pluralizing the class name of the
	# model.
	#
	# You can set this property to the table where the model must be stored/retrieved.
	#
	#
	#
	# Examples
	# --------
	#
	#	Class Person extends activeRecord {
	#		$protected $_table_name = 'people';
	#
	#		public function get_birthday_for_person($id) {
	#			return Person::find($id)->birthday;
	#		}
	#	}
	#
	# In this case the query to the database will be 'select * from people where id = ?'.
	protected $_table_name = false;

	# Active Record can serialize any object in text columns using serialize.
	# This makes it possible to store arrays and other non-mappable objects without doing any additional work.
	# Attributes on this property will be serialized on database and unserialized on load.
	protected $_serialize = array();

	# Active Record automatically timestamps create and update operations if the table has fields
	# named created_at/created_on or updated_at/updated_on.
	# Timestamping can be turned off by setting this property to false.
	protected $_record_timestamps = true;

	# _belongs_to
	# =====================
	#
	# Adds the following methods for retrieval and query for a single associated object for which
	# this object holds an id.
	#
	# * association: returns the associated object. nil is returned if none is found.
	# * association=(associate): assigns the associate object, extracts the primary key, and sets it as the foreign key.
	# * association_is_null: returns true if there is no associated object.
	# * build_association(attributes = array()): returns a new object of the associated type that has been
	# instantiated with attributes and linked to this object through a foreign key, but has not yet been saved.
	# * create_association(attributes = array()): returns a new object of the associated type that has been
	# instantiated with attributes, linked to this object through a foreign key, and that has already been
	# saved (if it passed the validation).
	#
	#
	#
	# The declaration can also include an options array to specialize the behavior of the association.
	#
	# Options
	# -------
	#
	# * class_name: specify the class name of the association. Use it only if that name can't be
	# inferred from the association name. So has_one :author will by default be linked to the Author
	# class, but if the real class name is Person, you‘ll have to specify it with this option.
	# * conditions: specify the conditions that the associated object must meet in order to be included
	# as a WHERE SQL fragment, such as authorized = 1.
	# * order: specify the order in which the associated objects are returned as an ORDER BY SQL fragment,
	# such as last_name, first_name DESC
	# * foreign_key: specify the foreign key used for the association. By default this is guessed to be
	# the name of the associated class in lower-case and _id suffixed. So a Person class that makes a
	# belongs_to association to a Boss class will use boss_id as the default foreign_key.
	# * counter_cache: caches the number of belonging objects on the associate class through the use of
	# increment_counter and decrement_counter. The counter cache is incremented when an object of this
	# class is created and decremented when it‘s destroyed. This requires that a column named #{table_name}_count
	# (such as comments_count for a belonging Comment class) is used on the associate class (such as a Post class).
	# You can also specify a custom counter cache column by providing a column name instead of a true/false value
	# to this option (e.g., "counter_cache" => "my_custom_counter").
	# Note: Specifying a counter_cache will add it to that model‘s list of readonly attributes using attr_readonly.
	# * joins: a complete join SQL fragment that specify the relation with the table. This option makes
	# the foreign_key option unservible.
	# * include: specify second-order associations that should be eager loaded when this object is loaded.
	# * polymorphic: specify this association is a polymorphic association by passing true. Note: If you‘ve enabled the counter cache, then you may want to add the counter cache attribute to the attr_readonly list in the associated classes (e.g. class Post; attr_readonly :comments_count; end).
	#
	#
	#
	# Examples
	# --------
	#
	#	protected $_belongs_to = array('firm' => array('foreign_key' => 'client_of'));
	#
	#	protected $_belongs_to = array('author' => array('class_name' => 'Person', 'foreign_key' => 'author_id'));
	#
	#	protected $_belongs_to = array('valid_coupon' => array(
	#		'class_name' => 'Coupon',
	#		'foreign_key' => 'coupon_id',
	#		'conditions' => 'discounts > 50'
	#	));
	protected $_belongs_to = array();

	# _has_one
	# ==================
	#
	# Adds the following methods for retrieval and query of a single associated object.
	# association(force_reload = false) - returns the associated object. nil is returned if none is found.
	#
	# * association: returns the associated object. nil is returned if none is found.
	# * association=(associate): assigns the associate object, extracts the primary key, and sets it as the foreign key.
	# * association_is_null: returns true if there is no associated object.
	# * build_association(attributes = array()): returns a new object of the associated type that has been
	# instantiated with attributes and linked to this object through a foreign key, but has not yet been saved.
	# * create_association(attributes = array()): returns a new object of the associated type that has been
	# instantiated with attributes, linked to this object through a foreign key, and that has already been
	# saved (if it passed the validation).
	#
	#
	#
	# Examples
	# --------
	#
	# An Account class declares has_one beneficiary, which will add:
	#
	#	$Account->beneficiary (similar to $Beneficiary->find("first", array("conditions" => array("account_id" => #{id})))
	#	$Account->build_beneficiary (similar to new Beneficiary(array("account_id" => id)))
	#	$Account->create_beneficiary (similar to $b = new Beneficiary(array("account_id" => id)); $b->save(); return $b)
	#
	#
	#
	# The declaration can also include an options array to specialize the behavior of the association.
	#
	# * class_name: specify the class name of the association. Use it only if that name can‘t be inferred
	# from the association name. So has_one "manager" will by default be linked to the Manager class, but
	# if the real class name is Person, you‘ll have to specify it with this option.
	# * conditions: specify the conditions that the associated object must meet in order to be included
	# as a WHERE SQL fragment, such as rank = 5.
	# * order: specify the order in which the associated objects are returned as an ORDER BY SQL fragment,
	# such as last_name, first_name DESC
	# * dependent: if set to "destroy", the associated object is destroyed when this object is. If set to
	# "delete", the associated object is deleted without calling its destroy method. If set to "nullify",
	# the associated object‘s foreign key is set to NULL. Also, association is assigned.
	# * foreign_key: specify the foreign key used for the association. By default this is guessed to be
	# the name of this class in lower-case and _id suffixed. So a Person class that makes a has_one
	# association will use person_id as the default foreign_key.
	# * include: specify second-order associations that should be eager loaded when this object is loaded.
	# * joins: a complete join SQL fragment that specify the relation with the table. This option makes
	# the foreign_key option unservible.
	# * through: Specifies a Join Model through which to perform the query. Options for "class_name" and
	# "foreign_key" are ignored, as the association uses the source reflection. You can only use a "through"
	# query through a belongs_to or has_one association on the join model.
	# * as: Specifies a polymorphic interface (See belongs_to).
	# * source: Specifies the source association name used by has_one "through" queries. Only use it if the name
	# cannot be inferred from the association. has_one => array("subscribers" => array("through" => "subscriptions"))
	# will look for either "subscribers" or "subscriber" on Subscription, unless a "source" is given.
	# * source_type: Specifies type of the source association used by has_one "through" queries where the source
	# association is a polymorphic belongs_to.
	#
	#
	# Examples
	# --------
	#
	#	$_has_one => array("credit_card" => array("dependent" => "destroy"))  # destroys the associated credit card
	#	$_has_one => array("credit_card" => array("dependent" => "nullify"))  # updates the associated records foreign key value to NULL rather than destroying it
	#	$_has_one => array("last_comment" => array("class_name" => "Comment", "order" => "posted_on"))
	#	$_has_one => array("project_manager" => array("class_name" => "Person", "conditions" => "role = 'project_manager'"))
	protected $_has_one = array();
	protected $_has_many = array();
	protected $_has_and_belongs_to_many = array();

	# Attributes named in this property are protected from mass-assignment, such as new object($attributes)
	# and $object->attributes($attributes). Their assignment will simply be ignored. Instead, you can use
	# the direct writer methods to do assignment. This is meant to protect sensitive attributes from being
	# overwritten by URL/form hackers.
	protected $_attr_protected = array();

	# Similar to the attr_protected property, this protects attributes of your model from mass-assignment,
	# such as new object($attributes) and $object->attributes($attributes) however, it does it in the opposite way.
	# This locks all attributes and only allows access to the attributes specified.
	# Assignment to attributes not in this list will be ignored and need to be set using the direct writer
	# methods instead. This is meant to protect sensitive attributes from being overwritten by URL/form hackers.
	# If you‘d rather start from an all-open default and restrict attributes as needed, have a look at attr_protected.
	protected $_attr_accessible = array();

	# This property disables the object modification.
	protected $_readonly = false;

	# This variable store the errors on validations.
	protected $_validation_errors = array();

	# These variables store the validations that will be executed on events.
	# Every validation has it's owns parameters. Read the validation class to see the options.
	protected $_validate_on_create = array();
	protected $_validate_on_update = array();
	protected $_validate_on_save = array();
	protected $_validates_acceptance_of = array();
	protected $_validates_confirmation_of = array();
	protected $_validates_each = array();
	protected $_validates_exclusion_of = array();
	protected $_validates_inclusion_of = array();
	protected $_validates_format_of = array();
	protected $_validates_length_of = array();
	protected $_validates_numericality_of = array();
	protected $_validates_presence_of = array();
	protected $_validates_uniqueness_of = array();

	# USED INTERNALLY
	# This property stores the object attributes and can't be accessed directly.
	protected $_attributes = array();

	# USED INTERNALLY
	# Before an attribute is setted, the real value sended to the __set is stored before it will
	# be casted to the real variable type.
	#
	# These values can be retrieved with <attribute>_before_typecast.
	protected $_attributes_before_typecast = array();

	# USED INTERNALLY
	# Data used as a store array for the object.
	protected $_data = array();

	# USED INTERNALLY
	# extract_dinamic_arguments($regexp, $method, $args)
	# ==================================================
	#
	# Extracs the arguments for dinamic operations like finders or counts called like
	# find_by_username_and_password or count_by_category.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * method: The method you called and is inexistent.
	# * args: The arguments you passed to the method.
	# * action: A variable passed by reference that will receive the action name.
	final private function extract_dinamic_arguments($method, $args, &$action) {
		$i = strpos($method, '_by_') + 4;
		$action = substr($method, 0, $i - 4);
		$conditions = substr($method, $i);
		$by_arguments = array('conditions' => array());
		foreach (explode('_and_', $conditions) as $index => $field)
			$by_arguments['conditions'][$field] = $args[$index];
		return count($args) == count($by_arguments['conditions']) ? $by_arguments : array_merge(array_pop($args), $by_arguments);
	}

	# USED INTERNALLY
	# __call($method, $args)
	# ======================
	#
	# **__call** is a *magic method* included in PHP5.
	#
	# If you call a method on an object and this method doesn't exist, **PHP** will fail
	# to find the function and check whether you have defined a **__call()** function.
	# If so, your **__call()** is used with the name of the method you tried to call and
	# the parameters you passed being passed in as parameters one and two respectively.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * method: The method you called and is inexistent.
	# * args: The arguments you passed to the method.
	final public function __call($method, $args) {
		if (strpos($method, 'set_') === 0) {
			$attribute = substr($method, 4);
			return $this->write_attribute($attribute, $args[0]);
		}
		if (strpos($method, 'find_by_') === 0 || strpos($method, 'find_all_by_') === 0) {
			$args = $this->extract_dinamic_arguments($method, $args, $action);
			return $this->dinamic_finder($method, $args, $action);
		}
		if (strpos($method, 'count_by_') === 0) {
			$args = $this->extract_dinamic_arguments($method, $args, $action);
			return $this->count($args);
		}
		$type = $this->type_relation_for($method, true);
		if ($type)
			return $this->{'find' . $type}($method, $args[0]);
		if (strpos($method, 'build_') === 0) {
			$association = substr($method, 6);
			return $this->build_association($association, $args[0]);
		}
		if (strpos($method, 'create_') === 0) {
			$association = substr($method, 7);
			return $this->create_association($association, $args[0]);
		}
		if (strpos($method, '_is_empty')) {
			$association = substr($method, 0, -9);
			$type = $this->type_relation_for($association);
			return $this->{'is_empty_' . ($type == '_has_one' || $type == '_belongs_to' ? 'association' : 'collection')}($association);
		}
		if (strpos($method, 'reset_') === 0) {
			$association = substr($method, 6);
			$type = $this->type_relation_for($association);
			return $this->{'reset_' . ($type == '_has_one' || $type == '_belongs_to' ? 'association' : 'collection')}($association);
		}
		if (strpos($method, 'clear_') === 0) {
			$association = substr($method, 6);
			$type = $this->type_relation_for($association);
			return $this->{'clear_' . ($type == '_has_one' || $type == '_belongs_to' ? 'association' : 'collection')}($association);
		}
		if (strpos($method, 'nullify_') === 0) {
			$association = substr($method, 8);
			$type = $this->type_relation_for($association);
			return $type == '_has_one' || $type == '_belongs_to' ? $this->nullify_association($association) : $this->nullify_collection($association, unidimensionalize($args));
		}
		if (strpos($method, 'delete_') === 0) {
			$association = substr($method, 7);
			$type = $this->type_relation_for($association);
			return $type == '_has_one' || $type == '_belongs_to' ? $this->delete_association($association) : $this->delete_collection($association, unidimensionalize($args));
		}
		if (strpos($method, 'destroy_') === 0) {
			$association = substr($method, 8);
			$type = $this->type_relation_for($association);
			return $type == '_has_one' || $type == '_belongs_to' ? $this->destroy_association($association) : $this->destroy_collection($association, unidimensionalize($args));
		}
		if (strpos($method, 'push_') === 0) {
			$association = substr($method, 5);
			return Inflector::is_plural($association) ? $this->push_collection($association, unidimensionalize($args)) : $this->push_collection(Inflector::pluralize($association), $args[0]);
		}
		if (strpos($method, 'delete_all_') === 0) {
			$association = substr($method, 11);
			return $this->delete_all_collection($association);
		}
		if (strpos($method, 'destroy_all_') === 0) {
			$association = substr($method, 12);
			return $this->destroy_all_collection($association);
		}
		if (strpos($method, 'nullify_all_') === 0) {
			$association = substr($method, 12);
			return $this->nullify_all_collection($association);
		}
		if (strpos($method, '_size')) {
			$association = substr($method, 0, -5);
			return $this->size_collection($association);
		}
		if (strpos($method, '_length')) {
			$association = substr($method, 0, -7);
			return $this->length_collection($association);
		}
		if (strpos($method, 'count_') === 0) {
			$collection = substr($method, 6);
			return $this->count_collection($collection, isset($args[0]) ? $args[0] : array());
		}
		if (strpos($method, 'find_first_') === 0) {
			$association = substr($method, 11);
			$collection = Inflector::pluralize($association);
			return $this->find_first_collection($collection, isset($args[0]) ? $args[0] : array());
		}
		if (strpos($method, 'find_last_') === 0) {
			$association = substr($method, 10);
			$collection = Inflector::pluralize($association);
			return $this->find_last_collection($collection, isset($args[0]) ? $args[0] : array());
		}
		if (strpos($method, 'find_') === 0) {
			$collection = substr($method, 5);
			return $this->find_collection($collection, $args[0], isset($args[1]) ? $args[1] : array());
		}
		if (strpos($method, 'sum_') === 0) {
			$collection = substr($method, 4);
			return $this->sum_collection($collection, $args[0], $args[1]);
		}
		if (strpos($method, 'uniq_') === 0) {
			$collection = substr($method, 5);
			return $this->uniq_collection($collection, $args[0]);
		}
		if (strpos($method, '_ids')) {
			$collection = substr($method, 0, -4);
			return $this->ids_collection($collection);
		}
		throw new Exception('Call to undefined method <b>' . $method . '</b>');
	}

	# USED INTERNALLY
	# __clone
	# =======
	#
	# In **PHP** when you assign one object to another object creates a reference copy and does not
	# create duplicate copy.
	# This would create a big mess as all the object will share the same memory defined for the
	# object. To counter this, PHP 5 has introduced clone method which creates an duplicate copy
	# of the object.
	# **__clone** `magic method` automatically get called whenever you call clone methods in PHP5.
	#
	# In **Comodo** it's used to reset object relations and primary key.
	final public function __clone() {
		$this->_attributes[$this->_primary_key] = $this->_attributes_before_typecast[$this->_primary_key] = false;
		foreach (array_merge($this->_belongs_to, $this->_has_one) as $relation => $options) {
			$object = is_numeric($relation) ? $options : $relation;
			if (isset($this->_attributes[$object]))
				$this->_attributes[$object] = clone $this->_attributes[$object];
		}

		foreach (array_merge($this->_has_many, $this->_has_and_belongs_to_many) as $relation => $options) {
			$table = is_numeric($relation) ? $options : $relation;
			if (isset($this->_attributes[$table]))
				foreach ($this->$table as $i => $object)
					$this->_attributes[$table[$i]] = clone $this->_attributes[$table[$i]];
		}
	}

	# USED INTERNALLY
	# __string()
	# ==========
	#
	# **__string** is a *magic method* included in PHP.
	# If you try to echo an object class, this method is called by PHP and the returned
	# value is printed.
	#
	# In **Comodo** it's used to print the id of the object. It's a cleaner way to print the
	# object's primary key value.
	#
	#
	#
	# Returns
	# -------
	#
	# * A string with the primary key value.
	#
	#
	#
	# Examples
	# --------
	#
	#	$user = new User
	#	echo $user => false
	#
	#	$winter = $Tag->find(5);
	#	echo $winter => 5
	final public function __toString() {
		return (string)$this->_attributes[$this->_primary_key];
	}

	# USED INTERNALLY
	# __get($attribute)
	# =================
	#
	# **__get** is a *magic method* included in PHP.
	# When an attribute of the class tried to be accessed, this method is called by PHP.
	#
	# In **Comodo** it's used to return the relational objects of this class.
	# When you try to get a field that is not in the class, this method will search
	# the correct relation for the given field.
	#
	# If a suitable relation is not found PHP will throw the default PHP error message.
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute you want to retrieve.
	#
	#
	#
	# Returns
	# -------
	#
	# * the given attribute value.
	#
	#
	#
	# Examples
	# --------
	#
	# No "contacts" field exists in the user model. "contacts" is a "has many" relation with the contact model.
	#
	#	$john = $User->find(3)
	#	$john->contacts
	#
	#
	# No "group" field exists in the user model. "group" is a "belongs to" relation with the group model
	#
	#	$dorothy = $User->find(5)
	#	$dorothy->group
	final public function &__get($attribute) {
		if (substr($attribute, -17) == '_before_type_cast')
			return $this->_attributes_before_typecast[str_replace('_before_type_cast', '', $attribute)];
		elseif (strpos($attribute, '_') === 0)
			return $this->$attribute;
		elseif (substr($attribute, -13) == '_confirmation')
			return $this->_attributes[$attribute];
		elseif (!$this->has_attribute($attribute) && !isset($this->_attributes[$attribute])) {
			$type = $this->type_relation_for($attribute);
			if ($type)
				$this->_attributes[$attribute] = $this->{'find' . $type}($attribute);
			else
				throw new Exception('Undefined attribute <b>' . $attribute . '</b> for #<' . $this->_class_name . '>');
		}
		return $this->_attributes[$attribute];
	}

	# USED INTERNALLY
	# __set($attribute, $value)
	# =========================
	#
	# **__set** is a *magic method* included in PHP.
	# When an attribute of the class tried to be setted, this method is called by PHP.
	#
	# In **Comodo** it's used to set the real cast of a field.
	# In PHP all variables setted by a database query are string. So, this method, assures
	# that the field is setted with the correct file type.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute to set.
	# * value: the value to set the attribute.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# Examples
	# --------
	#
	# In normal conditions PHP will do:
	#
	#	$john = $User->find(3)
	#	var_dump($john)
	#
	# And the id field will be a string.
	#
	#
	# With this method this is what success:
	#
	#	$dorothy = $User->find(5)
	#	var_dump($dorothy)
	#
	# And the id field is an integer.
	final public function __set($attribute, $value) {
		if (strpos($attribute, '_') === 0)
			$this->$attribute = $value;
		elseif ($this->has_attribute($attribute))
			if ((empty($this->_attr_accessible) || in_array($attribute, $this->_attr_accessible)) && (empty($this->_attr_protected) || !in_array($attribute, $this->_attr_protected)))
				$this->write_attribute($attribute, $value);
			else
				throw new Exception('Failed to set a protected attribute <b>' . $attribute . '</b> for #<' . $this->_class_name . '>');
		elseif (substr($attribute, - 13) == '_confirmation') {
			$type = $this->type_of_attribute(substr($attribute, 0, -13));
			$this->_attributes[$attribute] = $this->cast($value, $type);
		}
		elseif (substr($attribute, 0, -4) == '_ids') {
			$association = str_replace('_ids', '', $attribute);
			$this->set_ids_collection($association, $values);
		}
		else {
			$type = $this->type_relation_for($attribute);
			if ($type)
				$this->{'replace' . $type}($attribute, $value);
			else
				throw new Exception('Undefined attribute <b>' . $attribute . '</b> for #<' . $this->_class_name . '>');
		}
		return $this;
	}

	# USED INTERNALLY
	# __construct($attributes = array())
	# ==================================
	#
	# **__contruct** is the constructor method used in any class creation.
	# When a object is created, __construct is called by PHP.
	#
	# In **Comodo** it's used in the creation of every model. When a model is
	# created, the default value is setted for every model field.
	#
	# The first passed argument can be an array of values in the form of 'field' => 'value'
	# that will be setted in the model on it's initialization. This assignment will check
	# the **_attr_accessible** and **_attr_protected** object arrays.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attributes: an array with a pair 'attribute' => 'value' that you want to set on the object.
	# * avoid_protected_attributes: if true, the attributes will be assigned without the validation
	# of the attribute_protected property.
	#
	#
	# Examples
	# --------
	#
	#	$user = new User(array(
	#		'name' => 'John',
	#		'age' => 21
	#	))
	final public function __construct($attributes = array(), $avoid_protected_attributes = false) {
		$this->establish_connection();
		$this->_class_name = get_class($this);
		if (empty($this->_table_name))
			$this->_table_name = Inflector::tableize($this->_class_name);
		
		foreach (array_diff($this->columns(), array_keys($attributes)) as $field)
			$this->_set_value($field, $this->default_of_attribute($field));
		$this->attributes($attributes, $avoid_protected_attributes);
	}

	# establish_connection
	# ====================
	#
	# Set the connection to the database on the object.
	# There isn't a new connection for every object. This feature is implemented by keeping a
	# connection pool in Database that is an array indexed by database configuration.
	# If a connection is requested, the query will be made with this connection configuration.
	protected function establish_connection() {
		$this->_connection = DatabaseManager::get($this->_database);
	}

	# attributes($attributes = array())
	# =================================
	#
	# Assign on the object attributes the values on the given array.
	#
	# The attributes method will check on every field if it's on the _attr_accessible and _attr_protected
	# object arrays.
	#
	# * _attr_accessible array contains the fields on the object that are available from mass-assignment.
	# * _attr_protected array contains the fields on the object that are protected from mass-assignment
	#
	#
	#
	# Arguments
	# ---------
	#
	# * fields: an array with a pair 'attribute' => 'value' with the values that you want to assign to the object.
	# * avoid_protected_attributes: if true, the attributes will be assigned without the validation
	# of the attribute_protected property.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# Examples
	# --------
	#
	#	$user->attributes((
	#		'name' => 'James',
	#		'age' => 54
	#	))
	final public function attributes($attributes = array(), $avoid_protected_attributes = false) {
		if ($avoid_protected_attributes)
			foreach ($attributes as $attribute => $value)
				$this->write_attribute($attribute, $value);
		else
			foreach ($attributes as $attribute => $value)
				$this->$attribute = $value;
		return $this;
	}

	# read_attribute($attribute)
	# ==========================
	#
	# Gets the given attribute value. This is a more OO way to get the attribute value, but
	# $object->$attribute is an alternative instead.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute you want to get the value.
	#
	#
	#
	# Returns
	# -------
	#
	# * the attribute value.
	final public function read_attribute($attribute) {
		return $this->_attributes[$attribute];
	}

	# write_attribute($attribute, $value)
	# ===================================
	#
	# Updates the attribute with the specified value.
	# This and set_<attribute> are the way to set attributes on protected_attributes.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute you want to set.
	# * value: the value to set on the attribute.
	#
	#
	#
	# Returns
	# -------
	#
	# self.
	final public function write_attribute($attribute, $value) {
		if ($this->_readonly)
			throw new Exception('Trying to modify the attribute #<' . $attribute . '> on a read only object #<' . $this->_class_name . '>');
		else
			$this->_set_value($attribute, $value);
		return $this;
	}

	# USED INTERNALLY
	# _set_value($attribute, $value)
	# ==============================
	#
	# Sets the given attribute value on the field.
	# This method is used internally for passing the <read_only> attribute on objects on find operations.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute you want to set.
	# * value: the value to set on the attribute.
	final public function _set_value($attribute, $value) {
		$this->_attributes_before_typecast[$attribute] = $value;
		$type = $this->type_of_attribute($attribute);
		$this->_attributes[$attribute] = $this->cast($value, $type);
	}

	# USED INTERNALLY
	# cast($value, $type)
	# ===================
	#
	# Cast the given value for the given type.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * value: the value to cast.
	# * type: the type of the attribute content.
	#
	#
	#
	# Returns
	# -------
	#
	# The casted value.
	final private function cast($value, $type) {
		switch ($type) {
			case 'string': case 'text':
				return (string)$value;
			case 'decimal': case 'float':
				return (float)$value;
			case 'boolean':
				return (boolean)$value;
			case 'integer':
				return (integer)$value;
			default:
				return $value;
		}
	}

	# new_record()
	# ============
	#
	# Get if this object hasn‘t been saved yet — that is, a record for the object doesn‘t exist yet.
	#
	#
	#
	# Returns
	# -------
	#
	# * Returns true if this object hasn‘t been saved yet.
	final public function new_record() {
		return !$this->attribute_present($this->_primary_key);
	}

	# attribute_present($attribute)
	# =============================
	#
	# Check true if the specified attribute has been set by the user or by a database load and is
	# neither null nor empty.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: Attribute to search for.
	#
	#
	#
	# Returns
	# -------
	#
	# * Returns true if the specified attribute has been setted.
	final public function attribute_present($attribute) {
		$value = $this->$attribute;
		return !empty($value);
	}

	# exists($id_or_conditions, $values = array())
	# ============================================
	#
	# Checks whether a record exists in the database that matches conditions given.
	# These conditions can either be a single integer representing a primary key id to be found,
	# or a condition to be matched like using activeRecord#find.
	#
	# The id_or_conditions parameter can be an Integer or a String if you want to search the
	# primary key column of the table for a matching id or if you‘re looking to match against
	# a condition you can use an Array.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * id_or_conditions: the primary key on the object or a condition to search on a ***find***.
	# * values: values to pass to a query when a prepared statement is set on **id_or_conditions** param.
	#
	#
	#
	# Returns
	# -------
	#
	# true if the object exist in the database.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Post->exists(1)
	#
	#	$Post->exists('title = "does not exist"')
	#
	#	$Post->exists('user_id = ?', array('1'))
	#
	#	$Post->exists(array('title = ? and user_id = ?', array('My first post!', 1)))
	#
	#	$Post->exists(array('title' => 'My first post!', 'user_id' => 1))
	final public function exists($id_or_conditions, $values = array()) {
		if (is_numeric($id_or_conditions))
			return (boolean)$this->find($id_or_conditions);
		return (boolean)$this->find("first", array('conditions' => $id_or_conditions, $values));
	}

	# create($attributes = array())
	# =============================
	#
	# Creates an object and saves it to the database, if validations pass.
	# The attributes parameter can be an Array. This array describe the attributes on the object
	# that are to be created.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attributes: the attributes to set on the created object.
	#
	#
	#
	# Returns
	# -------
	#
	# * the new object.
	#
	#
	#
	# Examples
	# --------
	#
	#	$User->create(array('first_name' => 'Jamie'))
	final public function create($attributes = array()) {
		$object = new $this->_class_name($attributes);
		return $object->save() ? $object : false;
	}

	# duplicate($attributes = array())
	# ================================
	#
	# Make a clone of the object with all the given properties merged.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attributes: the attributes to set on the cloned object.
	# * avoid_protected_attributes: if true, the attributes will be assigned without the validation
	# of the attribute_protected property.
	#
	#
	#
	# Returns
	# -------
	#
	# * the cloned object.
	#
	#
	#
	# Examples
	# --------
	#
	#	$post = new Post
	#	$post1 = $post->find(1)
	#	$post2 = $post1->duplicate()
	#
	# In this example, post2 is exactly the same as post1 but without 'id'
	#
	#
	#	$post = new Post
	#	$post1 = $post->find(1)
	#	$post2 = $post1->duplicate(array('user_id' => 2))
	#
	# In this example, post2 is exactly the same as post1 but without 'id' and with a different
	# 'user_id' value.
	final public function duplicate($attributes = array(), $avoid_protected_attributes = false) {
		$clone = clone $this;
		return $clone->attributes($attributes, $avoid_protected_attributes);
	}

	# attributes_info($reload = false)
	# ================================
	#
	# Gets all info about the object's attributes on the database as an array with
	# their type, length and default value on database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * reload: if true, the attributes will be fetched from the database again.
	#
	#
	#
	# Returns
	# -------
	#
	# * an array with all the attributes as an associative array with name, length,
	# type and default value.
	#
	#
	#
	# Examples
	# --------
	#
	#	$post->attributes_info() => array(
	#		array('name' => 'id', 'length' => 11, 'type' => 'integer', 'null' => false),
	#		array('name' => 'user_id', 'length' => 11, 'type' => 'integer', 'null' => false),
	#		array('name' => 'title', 'length' => 200, 'type' => 'string', 'null' => false)
	#	)
	final public function attributes_info($reload = false) {
		static $fields = array();

		if (empty($fields) || $reload)
			$fields = $this->_connection->columns($this->_table_name);

		return $fields;
	}

	# columns($reload = false)
	# ========================
	#
	# Returns an array of all the columns with their names as keys and clones of their
	# objects as values.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * reload: if true, the columns will be fetched from the database again.
	#
	#
	#
	# Returns
	# -------
	#
	# * the attributes for the object on the database.
	final public function columns($reload = false) {
		static $columns = array();

		if (empty($columns) || $reload) {
			$fn = create_function('$field', 'return $field["name"];');

			$attributes = $this->attributes_info(true);

			$columns = array_map($fn, $attributes);
		}

		return $columns;
	}

	# has_attribute($attribute)
	# =========================
	#
	# Returns true if the given attribute is in the attributes array.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attributes: the attribute to search on the object's attributes.
	#
	#
	#
	# Returns
	# -------
	#
	# * true if the attribute exists on the object class.
	#
	#
	#
	# Examples
	# --------
	#
	#	post->has_attribute('title') => true
	#	post->has_attribute('user_id') => true
	#	post->has_attribute('this_attribute_does_not_exist') => false
	final public function has_attribute($attribute) {
		return in_array($attribute, $this->columns());
	}

	# type_of_attribute($attribute)
	# =============================
	#
	# Search the type of the attribute on the object's attributes.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute to search the type.
	#
	#
	#
	# Returns
	# -------
	#
	# * the type of the field on the database.
	#
	#
	#
	# Examples
	# --------
	#
	#	$post->type_of_attribute('user_id') => integer
	#	$post->type_of_attribute('title') => string
	final public function type_of_attribute($attribute) {
		static $types = array();
		if (empty($types))
			foreach ($this->attributes_info() as $field)
				$types[$field['name']] = $field['type'];
		return $types[$attribute];
	}

	# default_of_attribute($attribute)
	# ================================
	#
	# Search the default value of the attribute on the object's attributes.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute to search the default value.
	#
	#
	#
	# Returns
	# -------
	#
	# * the default value for the attribute on database.
	final public function default_of_attribute($attribute) {
		static $defaults = array();
		if (empty($defaults))
			foreach ($this->attributes_info() as $field)
				$defaults[$field['name']] = $field['default'];
		return $defaults[$attribute];
	}

	# inspect
	# =======
	#
	# Returns the contents of the record as a nicely formatted string.
	#
	#
	#
	# Returns
	# -------
	#
	# * the contents of the record as a formated string.
	final public function inspect() {
		return print_r($this, true);
	}

	# data($key = false, $value = false)
	# ==================================
	#
	# Access the container for all type of data that want to store on the model.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: The key to set the data.
	# * value: The value for the given key.
	#
	#
	#
	# Returns
	# -------
	#
	# * if no arguments is given, returns the complete data array.
	# * if one arguments is given, returns the data for the given key.
	# * if two arguments are given, sets the given data to the given key.
	final public function data($key = false, $value = false) {
		if ($key)
			if ($value) {
				$this->_data[$key] = $value;
				return $this;
			}
			else
				return $this->_data[$key];

		return $this->_data;
	}

	# query($query, $query_values = array())
	# ======================================
	#
	# Executes a custom sql query against your database and returns all the results. The results will
	# be returned as an array independent from the model you call.
	#
	# The sql parameter is a full sql query as a string. It will be called as is, there will be no
	# database agnostic conversions performed. This should be a last resort because using, for example,
	# MySQL specific terms will lock you to using that particular database engine or require you to
	# change your call if you switch engines.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * sql: The sql query to execute.
	# * values: Values needed on prepared statements.
	#
	#
	#
	# Returns
	# -------
	#
	# An associative array containing the information by the query (not activeRecord objects).
	#
	#
	#
	# Examples
	# --------
	#
	#	$Post->find_by_sql("SELECT p.title FROM posts p, comments c WHERE p.id = c.post_id")
	#	Array ( [0] => Array ( [title] => welcome aboard ) [1] => Array ( [title] => Raising the bar! ) )
	#
	# You can use the same string replacement techniques as you can with ActiveRecord#find
	#
	#	$Post->find_by_sql("SELECT title FROM posts WHERE author = ? AND created > ?", $author_id, $start_date)
	#	Array ( [0] => Array ( [title] => The Cheap Man Buys Twice ), ... )
	final public function query($query, $query_values = array()) {
		return $this->_connection->query($query, $query_values);
	}

	private $_savepoints_index = 1;
	private $_savepoints = array();

	# start_transaction()
	# ===================
	final public function start_transaction($new_savepoint = false) {
		$data = $this->_connection->begin_transaction();

		if ($data && $new_savepoint) {
			$name = 'active_record_' . $this->_savepoints_index++;
			array_unshift($this->_savepoints, $name);

			$this->_connection->create_savepoint($name);
		}

		return $data;
	}

	# rollback_transaction()
	# ======================
	final public function rollback_transaction() {
		if (count($this->_savepoints) > 0) {
			$savepoint = array_shift($this->_savepoints);
			return $this->_connection->rollback_to_savepoint($savepoint);
		}

		return $this->_connection->rollback_transaction();
	}

	# execute_transaction()
	# =====================
	final public function execute_transaction() {
		return $this->_connection->execute_transaction();
	}

	# find
	# ====
	#
	# Find operates with four different retrieval approaches:
	#
	# * Find by id: This can either be a specific id (1), a list of ids (1, 5, 6), or an array of ids (array(5, 6, 10)).
	#   If no record can be found for all of the listed ids, then RecordNotFound will be raised.
	# * Find first: This will return the first record matched by the options used. These options can either be specific
	#   conditions or merely an order. If no record can be matched, nil is returned.
	# * Find last: This will return the last record matched by the options used. These options can either be specific
	#   conditions or merely an order. If no record can be matched, nil is returned.
	# * Find all: This will return all the records matched by the options used. If no records are found, an empty array
	#   is returned.
	#
	# All approaches accept an options array as their last parameter.
	#
	#
	#
	# Options
	# -------
	#
	# * conditions: An SQL fragment like "administrator = 1" or array("user_name = ?", $username). See conditions in the intro.
	# * order: An SQL fragment like "created_at DESC, name".
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * limit: An integer determining the limit on the number of rows that should be returned.
	# * offset: An integer determining the offset from where the rows should be fetched. So at 5, it would skip rows 0 through 4.
	# * joins: An SQL fragment for additional joins like "LEFT JOIN comments ON comments.post_id = id" (rarely needed).
	# * include: Names associations that should be loaded alongside using LEFT OUTER JOINs. The symbols named refer
	#   to already defined associations. See eager loading under Associations.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to do a join but not
	#   include the joined columns.
	# * from: By default, this is the table name of the class, but can be changed to an alternate table name (or even the name
	#   of a database view).
	# * lock: An SQL fragment like "FOR UPDATE" or "LOCK IN SHARE MODE".
	#   lock => true gives connection's default exclusive lock, usually "FOR UPDATE".

	# * readonly: Mark the returned records read-only so they cannot be saved or updated.
	# * paginate => An array with the number of elements you want per page and a variable where to store the pages items
	#   * per_page => Number of elements on the page.
	#   * pages_item => A variable given by reference where the finder will store the pages variables.
	#
	#
	#
	# Examples for find by id
	# -----------------------
	#
	#	$Person->find(1)     # returns the object for ID = 1
	#	$Person->find(1, 2, 6)     # returns an array for objects with IDs in (1, 2, 6)
	#	$Person->find(array(7, 17))     # returns an array for objects with IDs in (7, 17)
	#	$Person->find([1])     # returns an array for the object with ID = 1
	#	$Person->find(1, array("conditions" => "administrator" => 1, "order" => "created_on DESC"))
	#
	# Note that returned records may not be in the same order as the ids you
	# provide since database rows are unordered. Give an explicit :order
	# to ensure the results are sorted.
	#
	#
	#
	# Examples for find first
	# -----------------------
	#
	#	$Person->find("first") # returns the first object fetched by SELECT * FROM people
	#	$Person->find("first", array("conditions" => array("user_name = ?", $user_name)))
	#	$Person->find("first", array("order" => "created_on DESC", "offset" => 5))
	#
	#
	#
	# Examples for find last
	# ----------------------
	#
	#	$Person->find("last") # returns the last object fetched by SELECT * FROM people
	#	$Person->find("last", array("conditions" => array("user_name = ?", $user_name)))
	#	$Person->find("last", array("order" => "created_on DESC", "offset" => 5))
	#
	#
	#
	# Examples for find all
	# ---------------------
	#
	#	$Person->find("all") # returns an array of objects for all the rows fetched by SELECT * FROM people
	#	$Person->find("all", array("conditions" => array("category IN (?)", $categories), "limit" => 50))
	#	$Person->find("all", array("offset" => 10, "limit" => 10))
	#	$Person->find("all", array("include" => array("account", "friends")))
	#	$Person->find("all", array("group" => "category"))
	#
	#
	#
	# Example for find with a lock
	# ----------------------------
	#
	# Imagine two concurrent transactions:
	# each will read person->visits == 2, add 1 to it, and save, resulting
	# in two saves of person->visits = 3.  By locking the row, the second
	# transaction has to wait until the first is finished; we get the
	# expected person->visits == 4.
	#
	#	$Person->start_transaction();
	#	$person = $Person->find(1, array("lock" => true));
	#	$person->visits += 1;
	#	$person->save(true);
	#	$Person->commit_transaction();
	#final public function find($type_or_id) {
	public function find($type_or_id) {
		$args = func_get_args();
		$options = $this->extract_find_options($args);
		switch ($type_or_id = array_shift($args)) {
			case 'first': return $this->find_first($options);
			case 'last': return $this->find_last($options);
			case 'all': return $this->_find($options);
			default:
				if (is_array($type_or_id))
					return $this->find_by_ids($type_or_id, $options);
				elseif (isset($args[0]) && is_numeric($args[0])) {
					$func = create_function('$v', 'return is_numeric($v);');
					return $this->find_by_ids(array_merge((array)$type_or_id, array_filter($args, $func)), $options);
				}
				else
					return $this->find_by_id($type_or_id, $options);
		}
	}

	# find_by_sql($sql, $values = array())
	# ======================================
	#
	# Executes a custom sql query against your database and returns all the results. The results will
	# be returned as an array of objects.
	#
	# The sql parameter is a full sql query as a string. It will be called as is, there will be no
	# database agnostic conversions performed. This should be a last resort because using, for example,
	# MySQL specific terms will lock you to using that particular database engine or require you to
	# change your call if you switch engines.
	#
	#
	#
	# Returns
	# -------
	#
	# An array of activeRecord objects.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * sql: The sql query to execute.
	# * values: Values needed on prepared statements.
	# * include_information: Information to load relation objects on the query.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Post->find_by_sql("SELECT p.title FROM posts p, comments c WHERE p.id = c.post_id")
	#	Array ( [0] => Post Object ( [title] => welcome aboard ) [1] => Post Object ( [title] => Raising the bar! ) )
	#
	# You can use the same string replacement techniques as you can with ActiveRecord#find
	#
	#	$Post->find_by_sql("SELECT title FROM posts WHERE author = ? AND created > ?", $author_id, $start_date)
	#	Array ( [0] => Post object ( [title] => The Cheap Man Buys Twice ), ... )
	final public function find_by_sql($sql, $values = array(), $include_information = array()) {
		$this->before_find();
		$this->_trigger_observers('before_find');
		$data = $this->_connection->query($sql, $values);
		$objects = empty($data) ? array() : $this->data_to_objects($data, $include_information);
		$this->after_find();
		$this->_trigger_observers('after_find');
		return $objects;
	}

	# USED INTERNALLY
	# dinamic_finder($method, $args)
	# ==============================
	#
	# **Dynamic attribute-based finders** are a cleaner way of getting (and/or creating) objects
	# by simple queries without turning to SQL. They work by appending the name of an
	# attribute to **find_by_** or **find_all_by_**, so you get finders like **$Person->find_by_user_name**,
	# **$Person->find_all_by_last_name**, **$Payment->find_by_transaction_id**.
	# So instead of writing **$Person->find('first', array('conditions' => array('user_name' => $user_name)))**,
	# you just do **$Person->find_by_user_name($user_name)**.
	# And instead of writing **$Person->find('all', array('conditions' => array('last_name' => $last_name)))**,
	# you just do **$Person->find_all_by_last_name($last_name)**.
	#
	# It's also possible to use multiple attributes in the same find by separating them with "_and_",
	# so you get finders like **$Person->find_by_user_name_and_password** or even **$Payment->find_by_purchaser_and_state_and_country**.
	# So instead of writing **$Person.find('first', array('conditions' => array('user_name' => $user_name, 'password' => $password)))**,
	# you just do **$Person->find_by_user_name_and_password($user_name, $password)**.
	#
	# It's even possible to use all the additional parameters to **find**. For example, the full
	# interface for **$Payment->find_all_by_amount** is actually **$Payment->find_all_by_amount($amount, $options)**.
	# And the full interface to **$Person->find_by_user_name** is actually **$Person->find_by_user_name($user_name, $options)**.
	# So you could call **$Payment->find_all_by_amount(50, array('order' => 'created_on'))**.
	#
	# The same dynamic finder style can be used to create the object if it doesn't already exist.
	# This dynamic finder is called with **find_or_create_by_** and will return the object if it already exists
	# and otherwise creates it, then returns it.
	#
	# Use the find_or_initialize_by_ finder if you want to return a new record without saving it first.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * method: The method call for a finder.
	# * args: Arguments for the find action.
	#
	#
	#
	# Returns
	# -------
	#
	# * For find_by_ method, a single object is returned.
	# * For find_all_by_ method, an array of objects is returned.
	# * For find_or_create_by_ find_or_initialize_by_ methods, the founded object or a new object is returned.
	#
	#
	#
	# Examples
	# --------
	#
	#  No "Summer" tag exists in database table
	#	$Tag->find_or_create_by_name('Summer') equal to $Tag->create(array('name' => 'Summer'))
	#
	#
	#  Now the "Summer" tag does exist in database table
	#	$Tag->find_or_create_by_name('Summer') equal to $Tag->find_by_name("Summer")
	#
	#
	#  No "Winter" tag exists in database table
	#	$winter = $Tag->find_or_initialize_by_name('Winter')
	final protected function dinamic_finder($method, $arguments, $action) {
		if ($action == 'find_all')
			return $this->find('all', $arguments);
		else {
			$object = $this->find('first', $arguments);
			if (!$object && ($action == 'find_or_initialize' || $action == 'find_or_create')) {
				$object = new $this->_class_name($by_arguments['conditions']);
				if ($action ==  'find_or_create')
					$object->save();
			}
			return $object;
		}
	}

	# USED INTERNALLY
	# find_by_id($id, $options)
	# =========================
	#
	# Find the specified object by the id and the passed options.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * id: The primary key to search for.
	# * options: Options for the find method.
	#
	#
	#
	# Returns
	# -------
	# * False if the given id is not founded.
	final private function find_by_id($id, $options) {
		$data = $this->find_by_ids(array($id), $options);
		return count($data) == 0 ? false : array_shift($data);
	}

	# USED INTERNALLY
	# find_by_ids($ids, $options)
	# ===========================
	#
	# Find the specified objects by the array of ids and the passed options.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * ids: An array of primary keys to search for.
	# * options: Options for the find method.
	#
	#
	#
	# Returns
	# -------
	#
	# * An array of objects or an empty array is there aren't results.
	final private function find_by_ids($ids, $options) {
		$options = $this->extract_find_options($options);

		$options['ids'] = $ids;

		return $this->_find($options);
	}

	# USED INTERNALLY
	# find_first($options)
	# ====================
	#
	# Find the first object that complains the specified options.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * options: Options for the find method.
	#
	#
	#
	# Returns
	# -------
	#
	# * One object or false if there are not results.
	final private function find_first($options) {
		$options['limit'] = 1;
		$data = $this->_find($options);
		return count($data) == 0 ? false : array_shift($data);
	}

	# USED INTERNALLY
	# find_last($options)
	# ===================
	#
	# Find the last object that complains the specified options.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * options: Options for the find method.
	#
	#
	#
	# Returns
	# -------
	#
	# * One object or false if there are not results.
	final private function find_last($options) {
		$options['order'] = empty($options['order']) ? $this->_table_name . '.' . $this->_primary_key . ' DESC' : (preg_match('/ DESC$/i', $options['order']) ? preg_replace('/ DESC$/i', ' ASC', $options['order']) : (preg_match('/ ASC$/i', $options['order']) ? preg_replace('/ ASC$/i', ' DESC', $options['order']) : $options['order'] . ' DESC'));
		return $this->find_first($options);
	}

	# USED INTERNALLY
	# _find($options)
	# ===============
	#
	# Execute the different find queries to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * options: Options for the find method.
	#
	#
	#
	# Returns
	# -------
	#
	# * The data array with the results.
	final protected function _find($options) {
		$values = $conditions = $include_information = array();
		$this->{$options['include'] ? '_construct_find_with_associations' : '_construct_find'}($options, $sql, $values, $include_information);
		$this->stringify_conditions($options['conditions'], $conditions, $values);
		$this->stringify_ids_conditions($options['ids'], ($options['joins'] || $options['include'] ? $this->_table_name . '.' : '') . $this->_primary_key, $conditions, $values);
		$options['conditions'] = array(implode(' AND ', $conditions), $values);
		$this->add_common_find_options($options, $sql);
		return $this->find_by_sql($sql, $values, $include_information);
	}

	# USED INTERNALLY
	# _construct_find($options, &$sql)
	# ================================
	#
	# Constructs a valid sql statement for simple queries (no includes on find method).
	#
	#
	#
	# Arguments
	# ---------
	#
	# * options: Options on the find method call.
	# * sql: An empty string where the sql will be stored.
	final private function _construct_find($options, &$sql) {
		$sql = 'SELECT ' . ($options['select'] ? $options['select'] : ($options['joins'] ? $options['from'] . '.*' : '*')) . ' FROM ' . $options['from'];
	}

	# USED INTERNALLY
	# _construct_find_with_associations($options, &$sql, &$include_information)
	# =========================================================================
	#
	# Constructs a valid sql statement for associated queries.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * options: Options on the find method call.
	# * sql: An empty string where the sql will be stored.
	# * include_information: An empty array to store the needed information to process to creation
	# of related objects.
	final private function _construct_find_with_associations($options, &$sql, &$values, &$include_information) {
		$include_information[$this->_table_name] = array('cn' => $this->_class_name, 'pk' => $this->_primary_key, 'r' => false, 's' => $this->_serialize);
		$select = 'SELECT ';
		$from = ' FROM ' . $this->_table_name;
		$this->add_fields_for($this->_table_name, $select);
		$this->add_includes($options['include'], $select, $from, $values, $include_information);
		$sql = $select . $from;
	}

	# USED INTERNALLY
	# add_common_find_options($options, &$sql)
	# ========================================
	#
	# Adds common options for the find and find_with_associations.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * options: The options to complete the query.
	# * sql: The query where to add the statements.
	final private function add_common_find_options($options, &$sql) {
		if ($options['joins']) $sql .= ' ' . $options['joins'];
		if ($options['conditions']) {
			$conditions = array_shift($options['conditions']);
			if ($conditions) $sql .= ' WHERE ' . $conditions;
		}
		if ($options['group']) $sql .= ' GROUP BY ' . $options['group'];
		if ($options['having']) $sql .= ' HAVING ' . $options['having'];
		if ($options['order']) $sql .= ' ORDER BY ' . $options['order'];
		if ($options['limit']) $this->_connection->add_limit_offset($sql, $options['limit'], $options['offset']);
		if ($options['lock']) $sql .= ' ' . $options['lock'];
	}

	# USED INTERNALLY
	# add_fields_for($alias, &$sql)
	# ======================================
	#
	# Adds the fields on the select statement query for finds with associations.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * object: The object to add fields.
	# * alias: The alias for the table.
	# * sql: The query where to add the selection fields.
	final protected function add_fields_for($alias, &$sql) {
		$fn = create_function('$f', 'return "' . $this->_table_name . '." . $f . " AS ' . $alias . '_" . $f;');
		$fields = array_map($fn, $this->columns());
		$sql .= implode(', ', $fields);
	}

	# USED INTERNALLY
	# add_includes($includes, &$select, &$from, &$include_information)
	# =======================================================================
	final protected function add_includes($includes, &$select, &$from, &$values, &$include_information) {
		if (is_string($includes))
			$includes = array_map('trim', explode(',', $includes));
		foreach ($includes as $include => $nested_includes) {
			if (is_numeric($include)) {
				$include = $nested_includes;
				$nested_includes = array();
			}
			else if (is_string($nested_includes))
				$nested_includes = array_map('trim', explode(',', $nested_includes));
			$type = $this->type_relation_for($include);
			$this->{'construct_find_with' . $type}($include, $nested_includes, $select, $from, $values, $include_information);
		}
	}

	# USED INTERNALLY
	# stringify_conditions($conditions, $conditions, $values)
	# =======================================================
	final protected function stringify_conditions($options_conditions, &$conditions, &$values) {
		if ($options_conditions) {
			if (is_string($options_conditions))
				$conditions[] = $options_conditions;
			elseif (is_array($options_conditions)) {
				// ex: array('user_id = ? and name = ?', 5, "adrian")
				// ex: array('user_id = ? and name = ?', array(5, "adrian"))
				if (is_numeric(key($options_conditions))) {
					$conditions[] = array_shift($options_conditions);
					$values = array_merge($values, unidimensionalize($options_conditions));
				}
				// ex: array('user_id' => 5, 'name' => 'adrian')
				else {
					$conditions[] = implode(' = ? AND ', array_keys($options_conditions)) . ' = ?';
					$values = array_merge($values, array_values($options_conditions));
				}
			}
		}
	}

	# USED INTERNALLY
	# stringify_ids_conditions($ids, $fields, $sql, $values)
	# ======================================================
	final protected function stringify_ids_conditions($ids, $field, &$conditions, &$values) {
		$ids = (array)$ids;
		$i = count($ids);
		if ($i > 0) {
			$conditions[] = $field . ($i == 1 ? ' = ?' : ' IN (?' . str_repeat(', ?', $i - 1) . ')');
			$values = array_merge($values, $ids);
		}
	}

	# extract_find_options($args)
	# ===========================
	#
	# Extracts the options for the find query from the given arguments. The options are
	#
	# * conditions: An SQL fragment like "administrator = 1" or array("user_name = ?", $username). See conditions in the intro.
	# * order: An SQL fragment like "created_at DESC, name".
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * limit: An integer determining the limit on the number of rows that should be returned.
	# * offset: An integer determining the offset from where the rows should be fetched. So at 5, it would skip rows 0 through 4.
	# * joins: An SQL fragment for additional joins like "LEFT JOIN comments ON comments.post_id = id" (rarely needed).
	# * include: Names associations that should be loaded alongside using LEFT OUTER JOINs. The symbols named refer
	#   to already defined associations. See eager loading under Associations.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to do a join but not
	#   include the joined columns.
	# * from: By default, this is the table name of the class, but can be changed to an alternate table name (or even the name
	#   of a database view).
	# * readonly: Mark the returned records read-only so they cannot be saved or updated.
	# * lock: An SQL fragment like "FOR UPDATE" or "LOCK IN SHARE MODE".
	#   lock => true gives connection's default exclusive lock, usually "FOR UPDATE".
	# * paginate => An array with the number of elements you want per page and a variable where to store the pages items
	#   * per_page => Number of elements on the page.
	#   * pages_item => A variable given by reference where the finder will store the pages variables.
	final private function extract_find_options($args) {
		$options = array('select' => false, 'from' => $this->_table_name, 'conditions' => false, 'group' => false, 'order' => false, 'joins' => false, 'include' => array(), 'having' => false, 'limit' => false, 'offset' => 0, 'readonly' => false, 'lock' => false, 'ids' => array());
		$keys = array_keys($options);
		foreach ($args as $val)
			if (is_array($val))
				foreach ($val as $param => $value)
					if (in_array($param, $keys))
						$options[$param] = $value;
		return $options;
	}

	# data_to_objects($data, $include_information)
	# ============================================
	#
	# Converts the given data array to an array of objects of the corresponding class
	# matching the includes option.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * data: The data returned by the database.
	# * include_information: The needed information to create the objects and assign them
	# to the correct parent.
	#
	#
	#
	# Returns
	# -------
	#
	# * The array of objects.
	final private function data_to_objects($data, $include_information) {
		if (empty($include_information)) {
			$processed_data = array();
			foreach ($data as $i => $row)
				foreach ($row as $attribute => $value)
					$processed_data[$i][$attribute] = in_array($attribute, $this->_serialize) ? unserialize($value) : $value;
			$fn = create_function('$r', '$o = new ' . $this->_class_name . '; foreach ($r as $f => $v) $o->_set_value($f, $v); return $o;');
			echo ( '$o = new ' . $this->_class_name . '; foreach ($r as $f => $v) $o->_set_value($f, $v); return $o;');
			print_r($processed_data);
			//exit;
			return array_map($fn, $processed_data);
		}

		foreach ($include_information as $tb => $options)
			$include_information[$tb]['last'] = false;

		foreach ($data as $record) {
			$row = array();
			foreach ($record as $column => $value) {
				$i = strpos($column, '_');
				$table = substr($column, 0, $i);
				$field = substr($column, $i + 1);
				$row[$table][$field] = in_array($column, $include_information[$table]['s']) ? unserialize($value) : $value;
			}
			foreach ($row as $table => $values) {
				$pk = $include_information[$table]['pk'];
				if ($include_information[$table]['last'] != $values[$pk]) {
					foreach ($include_information as $tb => $opt)
						if ($opt['r'] == $table)
							$items[$tb] = $items_ids[$tb] = array();

					$relation = $include_information[$table]['r'];
					if (!$relation || !in_array($values[$pk], $items_ids[$table])) {
						$ob = new $include_information[$table]['cn'];
						foreach ($values as $field => $value)
							$ob->_set_value($field, $value);
						$items[$table][] = $ob;
						$items_ids[$table][] = $values[$pk];
						if ($relation) {
							$v = $include_information[$table]['v'];
							$i = count($items[$relation]) - 1;
							if ($include_information[$table]['u'])	$items[$relation][$i]->_attributes[$v] = $ob;
							else									$items[$relation][$i]->_attributes[$v][] = $ob;
						}
					}
				}
				$include_information[$table]['last'] = $values[$pk];
			}
		}

		return $items[$this->_table_name];
	}

	# save
	# ====
	#
	# * No record exists: Creates a new record with values matching those of the object attributes.
	# * A record does exist: Updates the record with values matching those of the object attributes.
	#
	# Calling save(false) saves the model without running validations.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * validate: Set to false to avoid validations.
	#
	#
	#
	# Returns
	# -------
	#
	# * true if the record has been saved.
	final public function save($validate = true) {
		$new = $this->new_record();

		$valid = $validate ? $this->validations() : true;
		if ($valid) {
			$this->before_save();
			$this->_trigger_observers('before_save');

			$action = $new ? 'before_create' : 'before_update';
			$this->{$action}();
			$this->_trigger_observers($action);

			$this->save_belongs_to();

			$attributes = $values = array();
			foreach ($this->attributes_info() as $attribute)
				if ($attribute['name'] != $this->_primary_key) {
					$attributes[] = $attribute['name'];
					if (($attribute['name'] == 'created_at' && $new) || ($attribute['name'] == 'updated_at' && !$new) && $this->_record_timestamps)
						$this->$attribute['name'] = $attribute['type'] == 'integer' ? time() : date('Y-m-d H:i:s', time());
					if (($attribute['name'] == 'created_on' && $new) || ($attribute['name'] == 'updated_on' && !$new) && $this->_record_timestamps)
						$this->$attribute['name'] = $attribute['type'] == 'integer' ? time() : date('Y-m-d', time());
					$values[] = in_array($attribute['name'], $this->_serialize) ? serialize($this->$attribute['name']) : $this->$attribute['name'];
				}

			if ($new) {
				$query = 'INSERT INTO ' . $this->_table_name . ' (' . implode(', ', $attributes) . ') VALUES (?' . str_repeat(', ?', count($attributes) - 1) . ')';
				
				$this->set_id($this->_connection->query($query, $values));
			}
			else {
				$fn = create_function('$attr', 'return $attr . " = ?";');
				$data = array_map($fn, $attributes);
				$sql = 'UPDATE ' . $this->_table_name . ' SET ' . implode(', ', $data) . ' WHERE ' . $this->_primary_key . ' = ?';
				$values[] = $this->{$this->_primary_key};
				$this->_connection->query($sql, $values);
			}

			$this->save_has_one();
			$this->save_has_many();
			$this->save_has_and_belongs_to_many();

			$action = $new ? 'after_create' : 'after_update';
			$this->{$action}();
			$this->_trigger_observers($action);

			$this->after_save();
			$this->_trigger_observers('after_save');

			return true;
		}
	}

	# update_by_sql
	# =============
	#
	# Execute the given query to the database.
	# It must be a update query.
	#
	#
	#
	# Returns
	# -------
	#
	# * number of modified rows.
	#
	#
	#
	# Examples
	# --------
	#
	#	$object->update_by_sql('UPDATE users SET user = 'david' WHERE id = 5')
	#	$object->update_by_sql('UPDATE users SET user = ? WHERE id = ?', array('javier', 5))
	final public function update_by_sql() {
		$values = unidimensionalize(func_get_args());
		$query = array_shift($values);
		return $this->_connection->query($query, $values);
	}

	# update_attribute($attribute, $value)
	# ====================================
	#
	# Updates a single attribute and saves the record.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: Attribute to update.
	# * value: Value to assign.
	# * validate: Set to false to avoid validations.
	#
	#
	#
	# Returns
	# -------
	#
	# * true if the record has been saved.
	#
	#
	#
	# Examples
	# --------
	#
	#	$User->update_attribute('name', 'james')
	final public function update_attribute($attribute, $value, $validate = true) {
		return $this->update_attributes(array($attribute => $value), $validate);
	}

	# update_attributes($attributes, $validate = true)
	# ================================================
	#
	# Updates all the attributes from the passed-in Hash and saves the record.
	# If the object is invalid, the saving will fail and false will be returned.
	#
	# Calling update_attributes with second parameter to false) updates the model
	# without running validations.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attributes: An array with a pair 'attribute' => 'value' assign and save on object.
	# * validate: Set to false to avoid validations.
	#
	#
	#
	# Returns
	# -------
	#
	# * true if the record has been saved.
	#
	#
	#
	# Examples
	# --------
	#
	#	$User->update_attributes(array('name' => 'james', 'surname' => 'jameson'))
	final public function update_attributes($attributes, $validate = true) {
		$this->attributes($attributes);
		$valid = $validate ? $this->validations() : true;
		if ($valid) {
			$this->before_save();
			$this->_trigger_observers('before_save');

			$this->before_update();
			$this->_trigger_observers('before_update');

			$columns = $this->columns();
			if ($this->_record_timestamps && in_array('updated_at', $columns))
				$attributes['updated_at'] = $this->updated_at = $this->type_of_attribute('updated_at') == 'integer' ? time() : date('Y-m-d H:i:s', time());
			if ($this->_record_timestamps && in_array('updated_on', $columns))
				$attributes['updated_on'] = $this->updated_on = $this->type_of_attribute('updated_on') == 'integer' ? time() : date('Y-m-d', time());
			$fields = array_intersect($columns, array_keys($attributes));
			$sql = 'UPDATE ' . $this->_table_name . ' SET ' . implode(' = ?, ', $fields) . ' = ? WHERE ' . $this->_primary_key . ' = ?';
			$values = array();
			foreach ($fields as $attribute)
				$values[] = in_array($attribute, $this->_serialize) ? serialize($this->$attribute) : $this->$attribute;
			$values[] = $this->{$this->_primary_key};
			$data = $this->_connection->query($sql, $values);

			$this->save_belongs_to();
			$this->save_has_one();
			$this->save_has_many();
			$this->save_has_and_belongs_to_many();

			$this->after_update();
			$this->_trigger_observers('after_update');

			$this->after_save();
			$this->_trigger_observers('after_save');

			return true;
		}
	}

	# update
	# ======
	#
	# Updates the records given by the primary key given with the values given
	# on the last parameter.
	#
	#
	#
	# Returns
	# -------
	# * the objects saved or false otherwise
	#
	#
	#
	# Examples
	# --------
	#
	#	$object->update(1, array('age' => 23))
	#	$object->update(1, 5, 17, array('age' => 23))
	#	$object->update(array(1, 5, 17), array('age' => 23))
	final public function update() {
		$arguments = func_get_args();

		$attributes = array_pop($arguments);

		$ids = unidimensionalize($arguments);

		$correct = array();

		$objects = $this->find_by_ids($ids, array());

		foreach ($objects as $object) {
			$result = $object->update_attributes($attributes);

			if ($result)
				$correct[] = $object;
		}

		return count($correct) == 0 ? false : $correct;
	}

	# update_all($updates, $conditions = false)
	# =========================================
	#
	# Updates all records with details given if they match a set of conditions supplied, limits and order can
	# also be supplied.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * updates: A String of column and value pairs that will be set on any records that match conditions
	# * conditions: An SQL fragment like "administrator = 1" or array("user_name = ?", $username).
	# * options: Additional options are limit and/or order.
	#
	#
	#
	# Examples
	# --------
	#
	# Update all billing objects with the 3 different attributes given
	#	$Billing->update_all("category = 'authorized', approved = 1, author = 'David'")
	#
	# Update records that match our conditions
	#	$Billing->update_all("author = 'David'", "title LIKE '%Comodo%'")
	#
	# Update records that match our conditions but limit it to 5 ordered by date
	#	$Billing->update_all( "author = 'David'", "title LIKE '%Comodo%'",
	#                         :order => 'created_at', :limit => 5 )
	final public function update_all($updates, $conditions = false, $options = array()) {
		$options = array_merge(array('order' => false, 'limit' => false), $options);
		$sql = 'UPDATE ' . $this->_table_name . ' SET ' . $updates;
		if ($conditions) {
			$sql .= ' WHERE ';
			$this->stringify_conditions($conditions, $sql, $values);
		}
		if ($options['order'])	$this->add_order($options['order'], $sql);
		if ($options['limit'])	$this->add_limit($options['limit'], $sql);
		return $this->_connection->query($sql, $values);
	}

	# toggle
	# ======
	#
	# Turns the passed attributes that's currently true into false and vice versa.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# Examples
	# --------
	#
	#	$object->toggle('active', 'blocked')
	#	$object->toggle(array('active', 'blocked'))
	final public function toggle() {
		$attributes = unidimensionalize(func_get_args());
		foreach ($attributes as $attr)
			$this->$attr = !$this->$attr;
		return $this;
	}

	# toggle_and_save
	# ===============
	#
	# Turns the passed attributes that's currently true into false and vice versa and saves the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# Examples
	# --------
	#
	#	$object->toggle_and_save('active', 'blocked')
	#	$object->toggle_and_save(array('active', 'blocked'))
	final public function toggle_and_save() {
		$attributes = unidimensionalize(func_get_args());
		foreach ($attributes as $attr => $value)
			$attributes[$attr] = !$value;
		return $this->update_atributes($attributes);
	}

	# increment($attribute, $step = 1)
	# ================================
	#
	# Adds **step** to the attribute. Only makes sense for number-based attributes.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute to increment.
	# * step: the step to increment.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	final public function increment($attribute, $step = 1) {
		return $this->update_attribute($attribute, $this->$attribute + $step);
	}

	# decrement($attribute, $step = 1)
	# ================================
	#
	# Substracts **step** to the attribute. Only makes sense for number-based attributes.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * attribute: the attribute to decrement.
	# * step: the step to decrement.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	final public function decrement($field, $step = 1) {
		return $this->update_attribute($field, $this->$field - $step);
	}

	# delete
	# ======
	#
	# Delete an object (or multiple objects) where the id given matches the primary_key.
	# A SQL DELETE command is executed on the database which means that no callbacks are
	# fired off running this. This is an efficient method of deleting records that don‘t
	# need cleaning up after or other actions to be taken.
	#
	# Objects are not instantiated with this method.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * id: Can be either an Integer or an Array of Integers
	#
	#
	#
	# Examples
	# --------
	#
	# Delete a single object
	#
	#	$Todo->delete(1)
	#
	#
	# Delete multiple objects
	#
	#	$todos = [1,2,3]
	#	$Todo->delete($todos)
	final public function delete() {
		$this->get_delete_query_and_values(func_get_args(), $conditions, $values);
		return $this->delete_all($conditions, $values);
	}

	# delete_all
	# ==========
	#
	#
	#
	# Deletes the records matching conditions without instantiating the records first,
	# and hence not calling the destroy method and invoking callbacks. This is a single
	# SQL query, much more efficient than destroy_all.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * conditions: Conditions are specified the same way as with find method.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Post->delete_all("person_id = 5 AND (category = 'Something' OR category = 'Else')")
	final public function delete_all($conditions, $values = array()) {
		$query = 'DELETE FROM ' . $this->_table_name . ' WHERE ' . $conditions;
		return $this->delete_by_sql($query, $values);
	}

	# destroy
	# =======
	#
	# Destroy an object (or multiple objects) that has the given id, the object is instantiated first,
	# therefore all callbacks and filters are fired off before the object is deleted.
	# This method is less efficient than ActiveRecord#delete but allows cleanup methods and other actions to be run.
	# This essentially finds the object (or multiple objects) with the given id, creates
	# a new object from the attributes, and then calls destroy on it.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * id: Can be either an Integer or an Array of Integers
	#
	#
	#
	# Examples
	# --------
	#
	# Destroy a single object
	#
	#	$Todo->destroy(1)
	#
	#
	# Destroy multiple objects
	#
	#	$todos = [1,2,3]
	#	$Todo->destroy($todos)
	final public function destroy() {
		$this->get_delete_query_and_values(func_get_args(), $conditions, $values);
		return $this->destroy_all($conditions, $values);
	}

	# destroy_all
	# ===========
	#
	# Destroys the records matching conditions by instantiating each record and calling
	# the destroy method. This means at least 2*N database queries to destroy N records,
	# so avoid destroy_all if you are deleting many records. If you want to simply
	# delete records without worrying about dependent associations or callbacks, use the
	# much faster delete_all method instead.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * conditions: Conditions are specified the same way as with find method.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Person->destroy_all("last_login < '2004-04-04'")
	#
	# This loads and destroys each person one by one, including its dependent
	# associations and before_ and after_destroy callbacks.
	final public function destroy_all($conditions, $values = array()) {
		$data = array();
		$deletes = 0;
		foreach ($this->find('all', array('conditions' => array($conditions, $values))) as $object) {
			$object->before_delete();
			$object->_trigger_observers('before_delete');

			if ($object->delete()) {
				$deletes++;
				$object->delete_relations();

				$object->after_delete();
				$object->_trigger_observers('after_delete');
			}
			else
				$data[] = $object;
		}
		return count($data) > 0 ? $data : $deletes;
	}

	# delete_by_sql
	# =============
	#
	# Executes the delete statement and returns the number of rows affected.
	final public function delete_by_sql() {
		$values = unidimensionalize(func_get_args());
		$query = array_shift($values);
		return $this->_connection->query($query, $values);
	}

	# USED INTERNALLY
	# get_delete_query_and_values
	# ===========================
	#
	# Collects the ids of the objects to delete and makes the condition clausule.
	final private function get_delete_query_and_values($args, &$conditions, &$values) {
		$values = count($args) == 0 ? array($this->{$this->_primary_key}) : $args;
		$i = count($values);
		$conditions = $this->_primary_key . ($i == 1 ? ' = ?' : ' IN (?' . str_repeat(', ?', --$i) . ')');
	}

	# average($column_name, $options = array())
	# =========================================
	#
	# Calculates the average value on a given column. The value is returned as a float.
	# See calculate for examples with options.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Person->average('age')
	final public function average($column_name, $options = array()) {
		return $this->calculate('AVG', $column_name, $options);
	}

	# USED INTERNALLY
	# calculate($operation, column_name, $options = array())
	# ======================================================
	#
	# This calculates aggregate values in the given column. Methods for count, sum, average,
	# minimum, and maximum have been added as shortcuts. Options such as conditions, order,
	# group, having and joins can be passed to customize the query.
	#
	# There are two basic forms of output:
	#
	# * Single aggregate value: The single value is type cast to Fixnum for COUNT, Float for
	# AVG and the given column's type for everything else.
	# * Grouped values: This returns an ordered hash of the values and groups them by the
	# group option. It takes either a column name, or the name of a belongs_to association.
	#
	#	$values = $Person->maximum("age", array("group" => "last_name"));
	#	echo $values["Drake"];
	#	=> 43
	#
	#	$drake = $Family->find_by_last_name('Drake');
	#	$values = $Person->maximum("age", array("group" => "family")); # Person belongs_to family
	#	echo $values["drake"];
	#	=> 43
	#
	#
	#
	# Options
	# -------
	#
	# * conditions - An SQL fragment like "administrator = 1" or array("user_name = ?", $username).
	#   See conditions in the intro.
	# * include: Eager loading, see Associations for details. Since calculations don‘t load anything,
	#   the purpose of this is to access fields on joined tables in your conditions, order, or group clauses.
	# * joins - An SQL fragment for additional joins like "LEFT JOIN comments ON comments.post_id = id".
	#   (Rarely needed). The records will be returned read-only since they will have attributes that do
	#   not correspond to the table‘s columns.
	# * order - An SQL fragment like "created_at DESC, name" (really only used with GROUP BY calculations).
	# * group - An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * select - By default, this is * as in SELECT * FROM, but can be changed if you for example want
	#   to do a join, but not include the joined columns.
	# * distinct - Set this to true to make this a distinct calculation, such as SELECT COUNT(DISTINCT posts.id) …
	#
	#
	#
	# Examples
	# --------
	#
	#	$Person->average('age')     # SELECT AVG(age) FROM people...
	#	$Person->minimum('age', array('conditions' => array('last_name != ?', 'Drake')))     # Selects the minimum age for everyone with a last name other than 'Drake'
	#	$Person->minimum('age', array('having' => 'min(age) > 17', 'group' => $last_name))     # Selects the minimum age for any family without any minors
	final private function calculate($operation, $column_name, $options) {
		$options = array_merge(array(
			'distinct' => false,
			'conditions' => false,
			'group' => false,
			'order' => false,
			'joins' => false,
			'include' => array(),
			'having' => false
		), $options);

		$values = array();

		if ($options['group']) {
			$options['group_alias'] = $options['group'];

			$query = $this->construct_calculation_sql($operation, $column_name, $options, $values);

			return $this->_connection->query($query, $values);
		}

		$query = $this->construct_calculation_sql($operation, $column_name, $options, $values);

		$data = $this->_connection->query($query, $values);

		return array_shift(array_shift($data));
	}

	# USED INTERNALLY
	# construct_calculation_sql($operation, $column_name, $operation)
	# ===============================================================
	#
	# Construct the query for all calculation operations.
	final private function construct_calculation_sql($operation, $column_name, $options, &$values) {
		$options = array_merge(array(
			'distinct' => '',
			'joins' => '',
			'include' => array(),
			'conditions' => '',
			'group' => '',
			'having' => '',
			'order' => ''
		), $options);

		if (is_string($options['include'])) {
			$options['include'] = explode(',', $options['include']);
			$options['include'] = array_map('trim', $options['include']);
		}

		$workaround = $operation == 'COUNT' && $options['distinct'] ? !$this->_connection->database->_supports_count_distinct : false;

		$aggregate_alias = $this->get_alias_for($operation, $column_name);
		$column_alias = $this->get_alias_for($column_name);
		$group_alias = $this->get_alias_for($options['group']);

		$sql = $workaround ? 'select count(*) as ' . $column_alias : 'select ' . $operation . '(' . ($options['distinct'] ? 'distinct ' : '') . $column_name . ') as ' . $aggregate_alias;

		if ($workaround) {
			if ($options['group'])
				$sql .= ', ' . $options['group'] . ' AS ' . $group_alias;

			$sql .= ' from (select distinct ' . $column_name;
		}

		if ($options['group'])
			$sql .= ', ' . $options['group'] . ' as ' . $group_alias;

		$sql .= ' from ' . $this->_table_name;

		if ($options['include']) {
			$include_information = array(
				$this->_table_name => array(
					't' => $this->_table_name,
					'pk' => $this->_primary_key,
					'cn' => $this->_class_name,
					'r' => false,
					's' => $this->_serialize
				)
			);

			$this->add_includes($options['include'], $select, $sql, $values, $include_information);
		}

		if ($options['joins'])
			$sql .= ' ' . $options['joins'];

		if ($options['conditions']) {
			$conditions = $values = array();

			$this->stringify_conditions($options['conditions'], $conditions, $values);

			$sql .= ' where ' . implode(' and ', $conditions);
		}

		if ($workaround)
			$sql .= ') ' . $aggregate_alias;

		if ($options['having'])
			$sql .= ' having ' . $options['having'];

		if ($options['group'])
			$sql .= ' group by ' . $options['group'];

		if ($options['order'])
			$sql .= ' order by ' . $options['order'];

		return $sql;
	}

	# USED INTERNALLY
	# get_alias_for
	# =============
	#
	# Converts a given key to the value that the database adapter returns as
	# a usable column name.
	#
	#
	#
	# Examples
	# --------
	#
	#	users.id #=> users_id
	#	sum(id) #=> sum_id
	#	count(distinct users.id) #=> count_distinct_users_id
	#	count(*) #=> count_all
	final private function get_alias_for() {
		$args = func_get_args();

		$joined_args = implode(' ', $args);

		$lowered_joined_args = strtolower($joined_args);

		$cleaned_args = str_replace('*', 'all', $lowered_joined_args);

		$cleaned_non_words = preg_replace('/\W/', ' ', $cleaned_args);

		return preg_replace('/ /', '_', $cleaned_non_words);
	}

	# count($column_name = false, $options = array())
	# ===============================================
	#
	# Count operates using three different approaches.
	#
	# * Count all: By not passing any parameters to count, it will return a count of all the rows for the model.
	# * Count using column: By passing a column name to count, it will return a count of all the rows for the
	#   model with supplied column present
	# * Count using options will find the row count matched by the options used.
	#
	# The third approach, count using options, accepts an option hash as the only parameter. The options are:
	#
	# * conditions: An SQL fragment like "administrator = 1" or array("user_name = ?", $username).
	#   See conditions in the intro.
	# * joins: Either an SQL fragment for additional joins like "LEFT JOIN comments ON comments.post_id = id"
	#   (rarely needed) or named associations in the same form used for the "include" option, which will perform
	#   an INNER JOIN on the associated table(s). If the value is a string, then the records will be returned
	#   read-only since they will have attributes that do not correspond to the table‘s columns.
	#   Pass :readonly => false to override.
	# * include: Named associations that should be loaded alongside using LEFT OUTER JOINs. The symbols named
	#   refer to already defined associations. When using named associations, count returns the number of
	#   DISTINCT items for the model you‘re counting. See eager loading under Associations.
	# * order: An SQL fragment like "created_at DESC, name" (really only used with GROUP BY calculations).
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to do
	#   a join but not include the joined columns.
	# * distinct: Set this to true to make this a distinct calculation, such as SELECT COUNT(DISTINCT posts.id) …
	#
	#
	#
	# Examples for counting all
	# -------------------------
	#
	#	$Person->count()     # returns the total count of all people
	#
	#
	#
	# Examples for counting by column
	# -------------------------------
	#
	#	$Person->count("age")     # returns the total count of all people whose age is present in database
	#
	#
	#
	# Examples for count with options
	# -------------------------------
	#
	#	$Person->count(array("conditions" => "age > 26"))     # Performs a COUNT(*)
	#	$Person->count(array("conditions" => "age > 26 AND job.salary > 60000", "include" => "job"));     # because of the named association, it finds the DISTINCT count using LEFT OUTER JOIN.
	#	$Person->count(array("conditions" => "age > 26 AND job.salary > 60000", "joins" => "LEFT JOIN jobs on jobs.person_id = person.id"))     # finds the number of rows matching the conditions and joins.
	#	$Person->count('id', array("conditions" => "age > 26"))     # Performs a COUNT(id)
	final public function count($column_name = false, $options = array()) {
		if (is_array($column_name)) {
			$options = $column_name;

			$column_name = '*';
		}

		$column_name = empty($options['select']) ? ($column_name ? $column_name : '*') : $options['select'];

		return $this->calculate('COUNT', $column_name, $options);
	}

	# count_by_sql($query, $query_values = array())
	# =============================================
	#
	# Returns the result of an SQL statement that should only include a COUNT(*) in the SELECT part.
	# The use of this method should be restricted to complicated SQL queries that can‘t be executed
	# using the activeRecord::Calculations class methods. Look into those before using this.
	#
	#
	#
	# Options
	# -------
	#
	# * sql: An SQL statement which should return a count query from the database, see the example below
	#
	#
	#
	# Examples
	# --------
	#
	#	$Product->count_by_sql("SELECT COUNT(*) FROM sales s, customers c WHERE s.customer_id = c.id");
	final public function count_by_sql($query, $query_values = array()) {
		$data = $this->_connection->query($query, $query_values);
		$count = array_pop($data[0]);
		return (int)$count;
	}

	# maximum($column_name, $options = array())
	# =========================================
	#
	# Calculates the maximum value on a given column. The value is returned with the same data type of
	# the column. See calculate for examples with options.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Person->maximum('age')
	final public function maximum($column_name, $options = array()) {
		return $this->calculate('MAX', $column_name, $options);
	}

	# maximum($column_name, $options = array())
	# =========================================
	#
	# Calculates the minimum value on a given column. The value is returned with the same data type of
	# the column. See calculate for examples with options.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Person->minimum('age')
	final public function minimum($column_name, $options = array()) {
		return $this->calculate('MIN', $column_name, $options);
	}

	# sum($column_name, $options = array())
	# =====================================
	#
	# Calculates the sum of values on a given column. The value is returned with the same data type of
	# the column. See calculate for examples with options.
	#
	#
	#
	# Examples
	# --------
	#
	#	$Person->sum('age')
	final public function sum($column_name, $options = array()) {
		return $this->calculate('SUM', $column_name, $options);
	}

	# validations
	# ===========
	#
	# Executes all validations on object.
	final protected function validations() {
		$new = $this->new_record();
		$this->validate_callback($new ? '_validate_on_create' : '_validate_on_update');
		$this->validate_callback('_validate_on_save');

		$action = $new ? 'create' : 'update';
		$this->validates_acceptance_of($action);
		$this->validates_confirmation_of($action);
		$this->validates_each($action);
		$this->validates_exclusion_of($action);
		$this->validates_inclusion_of($action);
		$this->validates_format_of($action);
		$this->validates_length_of($action);
		$this->validates_numericality_of($action);
		$this->validates_presence_of($action);
		$this->validates_uniqueness_of($action);

		$this->validates_acceptance_of('save');
		$this->validates_confirmation_of('save');
		$this->validates_each('save');
		$this->validates_exclusion_of('save');
		$this->validates_inclusion_of('save');
		$this->validates_format_of('save');
		$this->validates_length_of('save');
		$this->validates_numericality_of('save');
		$this->validates_presence_of('save');
		$this->validates_uniqueness_of('save');

		return $this->count_validation_errors() === 0;
	}

	# has_validation_errors
	# =====================
	#
	# Check for validation errors on the model.
	#
	#
	#
	# Returns
	# -------
	#
	# * A boolean with true if has validation errors.
	final public function has_validation_errors() {
		return $this->count_validation_errors() > 0;
	}

	# count_validation_errors
	# =======================
	#
	# Catch the number of errors on validations.
	#
	#
	#
	# Returns
	# -------
	#
	# * An integer with the number of errors on all fields.
	final public function count_validation_errors() {
		$errors = 0;
		foreach ($this->_validation_errors as $field => $errors_on_field)
			$errors += count($errors_on_field);
		return $errors;
	}

	# count_validation_errors_on($field_error)
	# ================================================
	#
	# Catch the number of errors on validations in the specified field.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * field_error: The field to get the errors.
	#
	#
	#
	# Returns
	# -------
	#
	# * An integer with the number of errors on the given field.
	final public function count_validation_errors_on($field_error) {
		$errors = 0;
		foreach ($this->_validation_errors as $field => $errors_on_field)
			if ($field == $field_error)
				$errors += count($errors_on_field);
		return $errors;
	}

	# get_validation_errors
	# =====================
	#
	# Catch the errors on validations.
	#
	#
	#
	# Returns
	# -------
	#
	# * The errors on the object as a pair array with field => errors.
	final public function get_validation_errors() {
		return $this->_validation_errors;
	}

	# get_validation_errors_on($field_error)
	# ======================================
	#
	# Catch the errors on validations in the given field.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * field_error: The field to get the errors.
	#
	#
	#
	# Returns
	# -------
	#
	# * An array with the errors on the given field.
	final public function get_validation_errors_on($field_error) {
		return $this->_validation_errors[$field_error];
	}

	# add_error($field, $error_message = false)
	# =========================================
	#
	# Insert a new error message on validation errors.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * field: The field where to add the error.
	# * error_message: The message to add. If no error message is assigned a default message will
	# be stored.
	final protected function add_error($field, $error_message = false) {
		$this->_validation_errors[$field][] = $error_message ? $error_message : t('%s is invalid', $field);
	}

	# add_error_empty
	# ===============
	#
	# Insert a new error message on validation errors with a empty message.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * field: The field where to add the error.
	# * error_message: The message to add. If no error message is assigned a default empty
	# message will be stored.
	final protected function add_error_empty($field, $error_message = false) {
		$this->_validation_errors[$field][] = $error_message ? $error_message : t('%s can\'t be empty', $field);
	}

	# validate_callback
	# =================
	#
	# Check the given callbacks methods and fills an error if !false is returned.
	# If the returned value is a string, it is used as the validation error message.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * callback: The callback to traverse.
	final private function validate_callback($callback) {
		foreach ($this->$callback as $method) {
			$response = $this->$method();

			if ($response)
				$this->_validation_errors[$method][] = is_string($response) ? $response : t('validation failed on %s', $method);
		}
	}

	# parse_field_and_properties(&$field, &$properties)
	# =================================================
	#
	# Modify the field and properties if no properties are found.
	final private function parse_field_and_properties(&$field, &$properties) {
		if (is_numeric($field)) {
			$field = $properties;
			$properties = array();
		}
	}

	# evaluate_if_property($property, $if)
	# ====================================
	#
	# Evaluates the if property.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * property: The value of the property that is being evaluated. It can be a method on the
	# object that is expected to return a true value or a boolean value given by a condition.
	# * field: the field that is being evaluated.
	final private function evaluate_if_property($property, $field) {
		return method_exists($this, $property) ? $this->$property($field) : (boolean)$property;
	}

	# evaluate_on_property($property, $on)
	# ====================================
	#
	# Evaluates the on property.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * property: The value of the property that is being evaluated.
	# * on: The <moment> that is being evaluated (create, update, save).
	final private function evaluate_on_property($property, $on) {
		return (empty($property) && $on == 'save') || $property == $on;
	}

	# evaluate_nil_property($property, $field)
	# ========================================
	#
	# Evaluates the nil property.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * property: The value of the property that is being evaluated.
	# * field: The field that is being evaluated.
	final private function evaluate_nil_property($property, $field) {
		return $this->_attributes[$field] != '' || $property;
	}

	# validates_acceptance_of($on)
	# ============================
	#
	# Validates that the variables are accepted. The validates_acceptance_of property on
	# the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	# * accept: Specifies value that is considered accepted. The default value is a string "1".
	final private function validates_acceptance_of($on) {
		foreach ($this->_validates_acceptance_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('message' => t('%s must be accepted', $field), 'on' => false, 'if' => true, 'accept' => "1"), $properties);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && (!isset($this->$field) || $this->_attributes[$field] != $properties['accept']))
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_confirmation_of($on)
	# ==============================
	#
	# Validates the confirmation variables on the variables. . The validates_confirmation_of
	# property on the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	final private function validates_confirmation_of($on) {
		foreach ($this->_validates_confirmation_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('message' => t('the confirmation on %s does not agree', $field), 'on' => false, 'if' => true), $properties);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && (!isset($this->_attributes[$field . '_confirmation']) || $this->_attributes[$field] != $this->_attributes[$field . '_confirmation']))
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_each($on)
	# ===================
	#
	# Validates each attribute with a condition or a method. The validates_each property on
	# the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	# * accept: Specifies value that is considered accepted. The default value is a string "1".
	# * allow_nil: Skip validation if attribute is nil.
	# * function: Block or method to send the variable.
	final private function validates_each($on) {
		foreach ($this->_validates_each as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('message' => t('validation on %s failed', $field), 'on' => false, 'allow_nil' => false, 'if' => true, 'function' => false), $properties);
			if (empty($properties['function']))
				throw new Exception('Validation function is not defined');
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field) && !(method_exists($this, $properties['function']) ? $this->$properties['function']($field) : ($properties['function'] ? $properties['function'] : false)))
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_exclusion_of($on)
	# ===========================
	#
	# Validates that the variables values is distinct that the values passed on the 'in'
	# variable of the array. The validates_exclusion_of property on the object can specify
	# an array of options to configure the validation <moment>, message to show if the
	# validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * in: Array of values that are not valid.
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	# * allow_nil: Skip validation if attribute is nil.
	final private function validates_exclusion_of($on) {
		foreach ($this->_validates_exclusion_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('in' => false, 'message' => t('%s is reserved for %s', $this->$field, $field), 'on' => false, 'allow_nil' => false, 'if' => true), $properties);
			if (empty($properties['in']))
				throw new Exception('Exclusion values on validation are not defined');
			elseif (is_string($properties['in']))
				$properties['in'] = array($properties['in']);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field) && in_array($this->$field, $properties['in']))
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_inclusion_of($on)
	# ===========================
	#
	# Validates that the variables values is on the values passed on the 'in' variable
	# of the array. The validates_inclusion_of property on the object can specify
	# an array of options to configure the validation <moment>, message to show if the
	# validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * in: Array of values that are not valid.
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	# * allow_nil: Skip validation if attribute is nil.
	final private function validates_inclusion_of($on) {
		foreach ($this->_validates_inclusion_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('in' => false, 'message' => t('%s is not included on the list for %s', $this->$field, $field), 'on' => false, 'allow_nil' => false, 'if' => true), $properties);
			if (empty($properties['in']))
				throw new Exception('Inclusion values on validation are not defined');
			elseif (is_string($properties['in']))
				$properties['in'] = array($properties['in']);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field) && !in_array($this->$field, $properties['in']))
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_format_of($on)
	# ========================
	#
	# Validates that the variables values complains the format specified. The validates_format_of
	# property on the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * with: Expression to validate.
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	# * allow_nil: Skip validation if attribute is nil.
	final private function validates_format_of($on) {
		foreach ($this->_validates_format_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('with' => false, 'message' => t('%s is invalid', $field), 'on' => false, 'allow_nil' => false, 'if' => true), $properties);
			if (empty($properties['with']))
				throw new Exception('Format value on validation is not defined');
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field) && !preg_match($properties['with'], $this->_attributes[$field]))
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_length_of($on)
	# ========================
	#
	# Validates that the variables value size is on the limits. The validates_length_of
	# property on the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * minimum: The minimum size of the attribute.
	# * maximum: The maximum size of the attribute.
	# * is: The exact size of the attribute.
	# * within: A range specifying the minimum and maximum size of the attribute.
	# * allow_nil: Attribute may be nil; skip validation.
	# * too_long: The error message if the attribute goes over the maximum.
	# * too_short: The error message if the attribute goes under the minimum.
	# * wrong_length: The error message if using the :is method and the attribute is the wrong size.
	# * on: Specifies when this validation is active.
	# * if: Specifies a method or string to call to determine if the validation should.
	final private function validates_length_of($on) {
		foreach ($this->_validates_length_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('minimum' => false, 'maximum' => false, 'is' => false, 'within' => false, 'wrong_length' => 'wrong length for %s (must be %d characters)', 'too_long' => '%s too long (maximum %d characters)', 'too_short' => '%s too short (minimum %d characters)', 'on' => false, 'allow_nil' => false, 'if' => true), $properties);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field)) {
				if (!empty($properties['is']) && strlen($this->$field) != $properties['is'])				$this->_validation_errors[$field][] = t($properties['wrong_length'], $field, $properties['is']);
				elseif (!empty($properties['within'])) {
					if (strlen($this->$field) < $properties['within'][0])									$this->_validation_errors[$field][] = t($properties['too_short'], $field, $properties['within'][0]);
					if (strlen($this->$field) > $properties['within'][1])									$this->_validation_errors[$field][] = t($properties['too_long'], $field, $properties['within'][1]);
				}
				elseif (!empty($properties['minimum']) && strlen($this->$field) < $properties['minimum'])	$this->_validation_errors[$field][] = t($properties['too_short'], $field, $properties['minimum']);
				elseif (!empty($properties['maximum']) && strlen($this->$field) > $properties['maximum'])	$this->_validation_errors[$field][] = t($properties['too_long'], $field, $properties['maximum']);
			}

		}
	}

	# validates_numericality_of($on)
	# ==============================
	#
	# Validates that variables values are numbers. The validates_numericality_of property
	# on the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * message: Message to show if fails for not being a number.
	# * on: When validation should occur (create, update, save).
	# * only_integer: Specifies whether the value has to be an integer.
	# * minimum: The minimum value of the attribute.
	# * maximum: The maximum value of the attribute.
	# * within: A range specifying the minimum and maximum value of the attribute.
	# * too_big: The error message if the attribute goes over the maximum.
	# * too_small: The error message if the attribute goes under the minimum.
	# *	if: Specifies a method or string to call to determine if the validation should.
	# * allow_nil: Skip validation if attribute is nil.
	final private function validates_numericality_of($on) {
		foreach ($this->_validates_numericality_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('message' => t('value on %s is not a number', $field), 'only_integer' => false, 'minimum' => false, 'maximum' => false, 'within' => false, 'too_big' => 'value on %s is too big (maximum %d)', 'too_small' => 'value on %s is too small (minimum %d)', 'on' => false, 'allow_nil' => false, 'if' => true), $properties);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field)) {
				if (($properties['only_integer'] && !is_int($this->_attributes[$field])) || (!$properties['only_integer'] && !is_numeric($this->_attributes[$field])))
					$this->_validation_errors[$field][] = $properties['message'];
				elseif (!empty($properties['within'])) {
					if ($this->$field < $properties['within'][0])									$this->_validation_errors[$field][] = t($properties['too_small'], $field, $properties['within'][0]);
					if ($this->$field > $properties['within'][1])									$this->_validation_errors[$field][] = t($properties['too_big'], $field, $properties['within'][1]);
				}
				elseif (!empty($properties['minimum']) && $this->$field < $properties['minimum'])	$this->_validation_errors[$field][] = t($properties['too_small'], $field, $properties['minimum']);
				elseif (!empty($properties['maximum']) && $this->$field > $properties['maximum'])	$this->_validation_errors[$field][] = t($properties['too_big'], $field, $properties['maximum']);
			}
		}
	}

	# validates_presence_of($on)
	# ==========================
	#
	# Validates that variables values are not empty. The validates_presence_of property
	# on the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * message: Message to show if fails.
	# * on: When validation should occur (create, update, save).
	# *	if: Specifies a method or string to call to determine if the validation should.
	final private function validates_presence_of($on) {
		foreach ($this->_validates_presence_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('message' => t('%s can\'t be empty', $field), 'on' => false, 'if' => true), $properties);
			if (($this->evaluate_if_property($properties['if'], $field)) && $this->evaluate_on_property($properties['on'], $on) && $this->_attributes[$field] == '')
				$this->_validation_errors[$field][] = $properties['message'];
		}
	}

	# validates_uniqueness_of($on)
	# ============================
	#
	# Validates that variables values are not taken. The validates_uniqueness_of property
	# on the object can specify an array of options to configure the validation <moment>,
	# message to show if the validation fails and more. See options for more information.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * on: The <moment> that is being evaluated (create, update, save).
	#
	#
	#
	# Options
	# -------
	#
	# * message: Specifies a custom error message.
	# * scope: One or more fields by which to limit the scope of the uniqueness constraint.
	# * case_sensitive: Looks for an exact match. Ignored by non-text fields.
	# * allow_nil: If set to true, skips this validation if the attribute is null.
	# * if: Specifies a method or string to call to determine if the validation should.
	final private function validates_uniqueness_of($on) {
		foreach ($this->_validates_uniqueness_of as $field => $properties) {
			$this->parse_field_and_properties($field, $properties);
			$properties = array_merge(array('message' => t('value for %s has been taken', $field), 'on' => false, 'allow_nil' => false, 'scope' => array(), 'case_sensitive' => true, 'if' => true), $properties);
			if ($this->evaluate_if_property($properties['if'], $field) && $this->evaluate_on_property($properties['on'], $on) && $this->evaluate_nil_property($properties['allow_nil'], $field)) {
				$conditions = $properties['case_sensitive'] ? $field . ' LIKE ?' : 'LOWER(' . $field . ') LIKE ?';
				$values = array($this->$field);
				foreach ($properties['scope'] as $field) {
					$conditions .= ' AND ' . $field . ' = ?';
					$values[] = $this->$field;
				}
				if ($this->count($this->{$this->_primary_key}, array('conditions' => array($conditions, $values))) > 0 )
					$this->_validation_errors[$field][] = $properties['message'];
			}
		}
	}

	# USED INTERNALLY
	# type_relation_for($include)
	# ===========================
	#
	# Gets the relation type for the given relation key.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * include: the relation identifier on _belongs_to, _has_one, _has_many or _has_and_belongs_to_many
	# model configuration.
	#
	#
	#
	# Returns
	# -------
	#
	# * A string with the relation type (_belongs_to, _has_one, _has_many, _has_and_belongs_to_many).
	final protected function type_relation_for($include, $ignore_error = false) {
		$relations = array('_belongs_to', '_has_one', '_has_many', '_has_and_belongs_to_many');
		foreach ($relations as $relation)
			foreach ($this->$relation as $inclusion => $options)
				if ($inclusion === $include || $options === $include)
					return $relation;
		if (!$ignore_error)
			throw new Exception('Undefined relation <#' . $include . '> for #<' . $this->_class_name . '>');
	}

	# USED INTERNALLY
	# get_options($key, $relation_type, $base_options)
	# ================================================
	#
	# Search the options for the given relation index of the relation_type.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: The relation identifier on the model.
	# * relation_type: The relation type for the given key. It can be a _belongs_to, _has_one,
	# _has_many or _has_and_belongs_to_many.
	# * base_options: The base options for the given relation type.
	#
	#
	#
	# Returns
	# -------
	#
	# * An array with a complete options parameters for the given key and relation type.
	final protected function get_options($key, $relation_type, $base_options) {
		foreach ($this->$relation_type as $relation => $options)
			if ($options == $key)		return $base_options;
			elseif ($relation == $key)	return array_merge($base_options, $options);
	}

	# USED INTERNALLY
	# get_alias($aliases)
	# ===================
	#
	# Gets a valid alias for the model table.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * aliases: The actual aliases tables that are in use on the query.
	#
	#
	#
	# Returns
	# -------
	#
	# * A valid table name alias.
	final protected function get_alias($aliases) {
		$table = $this->_table_name;
		$exists = array_keys($aliases);
		$i = 0;
		while (in_array($table, $exists))
			$table = $this->_table_name . ++$i;
		return $table;
	}

	# USED INTERNALLY
	# build_association($association, $attributes)
	# ============================================
	#
	# Creates a new object of the associated type that has been instantiated with attributes
	# and linked to this object through a foreign key (if it's a has_one relation), but has
	# not yet been saved.
	#
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	# * attributes: The attributes to create the associated object.
	#
	#
	#
	# Returns
	# -------
	#
	# * The associated object.
	#
	#
	#
	# Examples
	# --------
	#
	#	$User->build_profile(array("image" => "avatar.jpg"))	// Profile
	#
	#
	#
	# NOTE: used only on belongs_to and has_one associations.
	final protected function build_association($association, $attributes) {
		$type = $this->type_relation_for($association);
		$options = $this->{'get_options_for' . $type}($association);
		$object = new $options['class_name']($attributes, true);
		switch ($type) {
			default: throw new Exception('Undefined belongs_to / has_one relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
			case '_has_one': $object->$options['foreign_key'] = $this->{$this->_primary_key};
			case '_belongs_to': return $this->_attributes[$association] = $object;
		}
	}

	# USED INTERNALLY
	# create_association($association, $attributes)
	# =============================================
	#
	# Creates a new object of the associated type that has been instantiated with attributes,
	# linked to this object through a foreign key, and that has already been saved (if it passed
	# the validation).
	#
	# The callbacks before_add and after_add will be executed before and after save the object.
	#
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	# * attributes: The attributes to create the associated object.
	#
	#
	#
	# Returns
	# -------
	#
	# * The associated object.
	#
	#
	#
	# Examples
	# --------
	#
	#	$User->create_profile(array("image" => "avatar.jpg"))	// Profile
	#
	#
	#
	# NOTE: used only on belongs_to and has_one associations.
	final protected function create_association($association, $attributes) {
		return $this->$association = $this->{'build_' . $association}($attributes);
	}

	# USED INTERNALLY
	# is_empty_association($association)
	# ==================================
	#
	# Get if there is an associated object for the given association key.
	#
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# True if there is no associated object.
	#
	#
	#
	# NOTE: used only on belongs_to and has_one associations.
	final protected function is_empty_association($association) {
		return !$this->attribute_present($association);
	}

	# reset_association($association)
	# ===============================
	#
	# Removes the object from the association on belongs_to and has_one relations without destroying the object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	final protected function reset_association($association) {
		unset($this->_attributes[$association]);
		return this;
	}

	# USED INTERNALLY
	# clear_association($association, $method = 'remove')
	# ===================================================
	#
	# Removes the given association from the model on database with the specified method.
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	# * method: The method to remove
	#	- nullify: Sets the foreign key on the object to null.
	#	- delete: Deletes the relation object with a delete statement. This method is the fastest delete way
	#	but will not fire the relation object callbacks.
	#	- destroy: Instantiates the object before remove it. This method fires the object callbacks.
	#	- remove: See the configured relation dependent attribute on the object and execute it.
	#
	#
	#
	# Returns
	# -------
	#
	# * self
	final protected function clear_association($association, $method = false) {
		$type = $this->type_relation_for($association);
		if (!$method) {
			$options = $this->{'get_options_for' . $type}($association);
			$method = $options['dependent'];
		}
		$this->before_remove($this->$association);
		$this->_trigger_observers('before_remove');
		$this->{$method . $type}($association);
		$this->after_remove($association);
		$this->_trigger_observers('after_remove');
		return $this;
	}

	# USED INTERNALLY
	# nullify_association($association)
	# =================================
	#
	# Removes the given association from the model nullifying the foreign key on the object.
	#
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self
	final protected function nullify_association($association) {
		return $this->clear_association($association, 'nullify');
	}

	# USED INTERNALLY
	# delete_association($association)
	# ================================
	#
	# Removes the given association from the model deleting the object without instantiating it, so callbacks
	# are not executed on the relation object.
	#
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self
	final protected function delete_association($association) {
		return $this->clear_association($association, 'delete');
	}

	# USED INTERNALLY
	# destroy_association($association)
	# =================================
	#
	# Removes the given association from the model deleting the object instantiating it, so callbacks are
	# executed on the relation object.
	#
	# This method is called with an alias for the association. See examples.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self
	final protected function destroy_association($association) {
		return $this->clear_association($association, 'destroy');
	}

	# USED INTERNALLY
	# ids_collection($collection)
	# ===========================
	#
	# Get the primary key of the associated objects type.
	#
	#
	#
	# Returns
	# -------
	#
	# * an array with the primary keys.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function ids_collection($collection) {
		$type = $this->type_relation_for($collection);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many':
				$objects = $this->{'find_' . $collection}('all');
				return ids($objects);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $collection . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# push_collection
	# ================
	#
	# Adds one or more objects to the collection.
	# The objects are assigned and saved on database.
	#
	#
	#
	# Returns
	# -------
	#
	# * self
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function push_collection() {
		$args = func_get_args();
		$association = array_shift($args);
		$type = $this->type_relation_for($association);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many':
				$options = $this->{'get_options_for' . $type}($association);
				foreach ($args as $object) {
					if (is_array($object))
						$object = new $options['class_name']($object);
					$this->before_add($object);
					$this->_trigger_observers('before_add');
					$this->{'add' . $type}($association, $object);
					$this->after_add($object);
					$this->_trigger_observers('after_add');
				}
				return $this;
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# replace_collection
	# ==================
	#
	# Replace this collection with other_array This will perform a diff and delete/add
	# only records that have changed. Changes will be made to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	# * objects: Objects to replace.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function replace_collection($association, $objects) {
		switch ($this->type_relation_for($association)) {
			case '_has_many': case '_has_and_belongs_to_many':
				$this->reset_collection($association);
				$old_objects = $this->$association;
				foreach ($old_objects as $object)
					if (!in_array($object, $objects))
						$this->delete_collection($association, $object);
				foreach ($objects as $object)
					if (!in_array($object, $old_objects))
						$this->push_collection($association, $object);
				return $this;
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# reset_collection($association)
	# ==============================
	#
	# Removes every object from the collection on has_many and has_and_belongs_to_many relations without
	# destroying the objects.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	final protected function reset_collection($association) {
		unset($this->_attributes[$association]);
		return this;
	}

	# clear_collection
	# ================
	#
	# Removes all records from this association.
	#
	# * On has_and_belongs_to_many associations, removes their associations from the join table.
	# * On has_many associations sets their foreign keys to NULL. This will also destroy the objects
	# if they‘re declared as belongs_to and dependent on this model.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * associations: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function clear_collection($association) {
		$this->reset_collection($association);
		$type = $this->type_relation_for($association);
		$options = $this->{'get_options_for' . $type}($association);
		$action  == 'destroy' ? 'destroy_collection' : 'delete_collection';
		foreach ($this->$association as $object)
			$this->$action($association, $object);
		return $this;
	}

	# delete_collection
	# =================
	#
	# Removes one or more objects from the collection.
	#
	# * On has_and_belongs_to_many associations, removes their associations from the join table.
	# * On has_many associations sets their foreign keys to NULL. This will also destroy the objects
	# if they‘re declared as belongs_to and dependent on this model.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function delete_collection() {
		$objects = func_get_args();
		$association = array_shift($objects);
		$type = $this->type_relation_for($association);
		$options = $this->{'get_options_for' . $type}($association);
		foreach ($objects as $object) {
			$this->before_remove($object);
			$this->_trigger_observers('before_remove');
			$this->{'delete' . $type}($object);
			$this->after_remove($object);
			$object->_trigger_observers('after_remove');
		}
		return $this;
	}

	# delete_all_collection
	# =====================
	#
	# Removes one or more objects from the collection.
	#
	# * On has_and_belongs_to_many associations, removes their associations from the join table.
	# * On has_many associations sets their foreign keys to NULL. This will also destroy the objects
	# if they‘re declared as belongs_to and dependent on this model.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function delete_all_collection($association) {
		$type = $this->type_relation_for($association);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many': return $this->{'delete_all' . $type}($association, $this->$association);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# destroy_collection
	# ==================
	#
	# Removes one or more objects from the collection.
	#
	# * On has_and_belongs_to_many associations, removes their associations from the join table.
	# * On has_many associations sets their foreign keys to NULL. This will also destroy the objects.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function destroy_collection() {
		$objects = func_get_args();
		$association = array_shift($objects);
		$type = $this->type_relation_for($association);
		$options = $this->{'get_options_for' . $type}($association);
		foreach ($objects as $object)
			$this->{'destroy' . $type}($object);
		return $this;
	}

	# destroy_all_collection
	# ======================
	#
	# Removes one or more objects from the collection.
	#
	# * On has_and_belongs_to_many associations, removes their associations from the join table.
	# * On has_many associations sets their foreign keys to NULL. This will also destroy the objects.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function destroy_all_collection($association) {
		$type = $this->type_relation_for($association);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many': return $this->{'destroy_all' . $type}($association, $this->$association);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# is_empty_collection($association)
	# =================================
	#
	# Get if there are no associated objects on the collection.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * True if the objects has no associated objects of the given association key.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function is_empty_collection($association) {
		return $this->length_collection($association) == 0;
	}

	# size_collection($association)
	# ==============================
	#
	# This works as a combination of length_collection and count_collection. If the collection has
	# already been loaded, it will return its length just like calling #length. If it hasn't been
	# loaded yet, it's like calling #count.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * The number of associated objects for the given association key.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function size_collection($association) {
		$type = $this->type_relation_for($association);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many': return $this->{empty($this->$association) ? 'count_collection' : 'length_collection'}($association);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# length_collection($association)
	# ==============================
	#
	# This always loads the contents of the association into the object if it has not been
	# loaded previously before return the size of the collection.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * The number of associated objects for the given association key.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function length_collection($association) {
		$type = $this->type_relation_for($association);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many':
				$this->$association;
				return count($this->$association);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $association . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# count_collection($association, $params)
	# =======================================
	#
	# Determine the number of elements with an SQL COUNT query for the given association.
	# You can also specify conditions to count only a subset of the associated elements.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	# * conditions: Conditions for the sql count query.
	#
	#
	#
	# Returns
	# -------
	#
	# * The number of associated objects for the given association key.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function count_collection($collection, $params) {
		$type = $this->type_relation_for($collection);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many':
				if ($this->new_record())
					return 0;

				$options = $this->{'get_options_for' . $type}($collection);
				$object = new $options['class_name'];
				$options = array_merge($options, $params);
				$conditions = array($object->_table_name . '.' . $options['foreign_key'] . ' = ?');
				$values = array($this->{$this->_primary_key});
				$this->stringify_conditions($options['conditions'], $conditions, $values);
				$options['conditions'] = array(implode(' AND ', $conditions), $values);

				return $object->count('*', $options);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $collection . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# find_collection
	# ===============
	#
	# Find the objects on database that suits the given conditions and is related with
	# the object.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function find_collection($collection, $mode, $params) {
		$type = $this->type_relation_for($collection);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many':
				if ($this->new_record())
					return false;

				$options = $this->{'get_options_for' . $type}($collection);
				$object = new $options['class_name'];
				$options = array_merge($options, $params);
				$conditions = array($object->_table_name . '.' . $options['foreign_key'] . ' = ?');
				$values = array($this->{$this->_primary_key});
				$this->stringify_conditions($options['conditions'], $conditions, $values);
				$options['conditions'] = array(implode(' AND ', $conditions), $values);

				return $object->find($mode, $options);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $collection . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# find_first_collection
	# =====================
	#
	# Find the first object on database that suits the given conditions and is related with
	# the object.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function find_first_collection($collection, $params) {
		return $this->find_collection($collection, 'first', $params);
	}

	# find_last_collection
	# ====================
	#
	# Find the last object on database that suits the given conditions and is related with
	# the object.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function find_last_collection($collection, $params) {
		return $this->find_collection($collection, 'last', $params);
	}

	# sum_collection
	# ==============
	#
	# Calculate the sum on the collection using SQL.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function sum_collection($collection, $column, $params) {
		$type = $this->type_relation_for($collection);
		switch ($type) {
			case '_has_many': case '_has_and_belongs_to_many':
				if ($this->new_record())
					return false;

				$options = $this->{'get_options_for' . $type}($collection);
				$object = new $options['class_name'];
				$options = array_merge($options, $params);
				$conditions = array($object->_table_name . '.' . $options['foreign_key'] . ' = ?');
				$values = array($this->{$this->_primary_key});
				$this->stringify_conditions($options['conditions'], $conditions, $values);
				$options['conditions'] = array(implode(' AND ', $conditions), $values);

				return $object->sum($column, $options);
			default: throw new Exception('Undefined has_many / has_and_belongs_to_many relation named <b>' . $collection . '</b> for #<' . $this->_class_name . '>');
		}
	}

	# uniq_collection
	# ===============
	#
	# Gets the objects on the collection without duplicates.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function uniq_collection($associations) {
		$ids = $objects = array();
		foreach ($this->$association as $object) {
			$id = $object->{$object->_primary_key};
			if (!in_array($id, $ids))
				$objects[] = $object;
			$ids[] = $id;
		}
		return $objects;
	}

	# collection_singular_ids($association)
	# =====================================
	#
	# Get an array of the associated objects ids.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	#
	#
	#
	# Returns
	# -------
	#
	# * An array with all primary keys from the association objects.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function collection_singular_ids($association) {
		$fn = create_function('$o', 'return $o->{$o->_primary_key};');
		return array_map($fn, $this->$association);
	}

	# set_ids_collection($association, $ids)
	# ======================================
	#
	#
	#
	# Arguments
	# ---------
	#
	# * association: The association key on the object.
	# * ids: The primary key for the objects to search.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# NOTE: used only on has_many and has_and_belongs_to_many associations.
	final protected function set_ids_collection($association, $ids) {
		$type = $this->type_relation_for($association);
		$options = $this->{'get_options_for' . $type}($association);
		$object = new $options['class_name']($attributes, true);
		return $this->push_collection($association, $object->find($ids));
	}

	# delete_relations()
	# ==================
	#
	# Cleans all relations from the object after a destroy operation.
	final protected function delete_relations() {
		$relations = array('_belongs_to', '_has_one', '_has_many', '_has_and_belongs_to_many');
		foreach ($relations as $relation_type)
			foreach ($this->$relation_type as $relation => $options) {
				$relation = is_string($options) ? $options : $relation;
				$type = $this->type_relation_for($relation);
				$this->{'remove' . $type}($relation);
			}
	}

	# USED INTERNALLY
	# get_options_for_belongs_to($key)
	# ================================
	#
	# Get the options for the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# Get the options for the given relation key.
	#
	#
	#
	# Options
	# -------
	#
	# * class_name: specify the class name of the association. Use it only if that name can‘t be inferred
	# from the association name. So has_one "manager" will by default be linked to the Manager class, but
	# if the real class name is Person, you‘ll have to specify it with this option.
	# * foreign_key: specify the foreign key used for the association. By default this is guessed to be
	# the name of this class in lower-case and _id suffixed. So a Person class that makes a has_one
	# association will use person_id as the default foreign_key.
	# * through: Specifies a Join Model through which to perform the query.
	# * polymorphic: Specify this association is a polymorphic association by passing true. Note: If you‘ve
	# * as: Specifies the prefix used on the fields *_id and *_type on a polymorphic relation.
	# * source_type: On a polymorphic relation it specifies the type of the relation that will be searched
	# on the relation table (value on the <table>_type field). By default is the object class name.
	# * source: Specifies the source association name used on through queries on a polymorphic relation.
	# Only use it if the name cannot be inferred from the association.
	# has_one "person", through => "subscriptions" will look for "person" on Subscription relation object,
	# unless a source is given.
	# * dependent: if set to "destroy", the associated object is destroyed when this object is. If set to
	# "delete", the associated object is deleted without calling its destroy method. If set to "nullify"
	# (default), the associated object‘s foreign key is set to NULL.
	# * finder_sql: specify a complete SQL statement to fetch the association. This is a good way to go
	# for complex associations that depend on multiple tables.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to
	# do a join but not include the joined columns.
	# * joins: a complete join SQL fragment that specify the relation with the table. This option makes
	# the foreign_key option unservible.
	# * include: specify second-order associations that should be eager loaded when this object is loaded.
	# * conditions: specify the conditions that the associated object must meet in order to be included
	# as a WHERE SQL fragment, such as rank = 5.
	# * order: specify the order in which the associated objects are returned as an ORDER BY SQL fragment,
	# such as last_name, first_name DESC
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * having: Specify the group conditions that the associated object must meet in order to be included on
	# finds when group option is used.
	# * limit: An integer determining the limit on the number of rows that should be returned.
	# * offset: An integer determining the offset from where the rows should be fetched. So at 5, it would
	# skip the first 4 rows.
	# * lock: An SQL fragment like "FOR UPDATE" or "LOCK IN SHARE MODE".
	#   lock => true gives connection's default exclusive lock, usually "FOR UPDATE".
	#
	#
	#
	# Returns
	# -------
	#
	# * An associative array with all posible options.
	final protected function get_options_for_belongs_to($key) {
		$base_options = array('class_name' => Inflector::classify($key), 'foreign_key' => $key . '_id', 'through' => false, 'as' => false, 'source_type' => $key, 'polymorphic' => false, 'dependent' => 'nullify', 'finder_sql' => false, 'select' => false, 'include' => array(), 'joins' => false, 'conditions' => false, 'order' => false, 'group' => false, 'having' => false, 'limit' => false, 'offset' => false, 'lock' => false);
		foreach ($this->_belongs_to as $relation => $options)
			if ($options == $key)		return $base_options;
			elseif ($relation === $key)	return array_merge($base_options, $options);
	}

	# USED INTERNALLY
	# construct_find_with_belongs_to($include, $nested_includes, &$select, &$from, &$values, &$include_information)
	# ==========================================================================================================
	#
	# Constructs the sql for a include operation with the belongs to given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * include: the include relation key.
	# * nested_includes: includes that are nested to the include key object.
	# * select: the select statement that will be completed.
	# * from: the from statement that will be completed.
	# * values: values that will be passed to the database query for prepared statements.
	# * include_information: an array with the include information needed to process the relations on the returned
	# database data.
	final protected function construct_find_with_belongs_to($include, $nested_includes, &$select, &$from, &$values, &$include_information) {
		$options = $this->get_options_for_belongs_to($include);
		$object = new $options['class_name'];

		$alias = $object->get_alias($include_information);

		$select .= ', ';
		$object->add_fields_for($alias, $select);

		$include_information[$alias] = array('cn' => $object->_class_name, 'pk' => $object->_primary_key, 'r' => $this->_table_name, 's' => $object->_serialize, 'u' => true, 'v' => $include);

		$this->add_belongs_to_join($options, $include, $object, $alias, $from, $values, $include_information, $this->_table_name, $this->_primary_key);

		$object->add_includes($nested_includes, $select, $from, $values, $include_information);
	}

	# USED INTERNALLY
	# add_belongs_to_join($options, $include, $object, $alias, &$from, &$values, &$include_information, $table_related, $pk_related)
	# ===========================================================================================================================
	#
	# Completes the from statement for a include operation.
	protected function add_belongs_to_join($options, $include, $object, $alias, &$from, &$values, &$include_information, $table_related, $pk_related) {
		if ($options['through']) {
			$options_through = $this->get_options_for_belongs_to($options['through']);
			$through_object = new $options_through['class_name'];

			$through_alias = $through_object->get_alias($include_information);

			$object->add_belongs_to_join($options_through, $include, $through_object, $through_alias, $from, $values, $include_information, $table_related, $pk_related);

			$options = $through_object->get_options_for_belongs_to($include);

			$table_related = $through_alias;
			$pk_related = $through_object->_primary_key;
		}

		$from .= ' LEFT OUTER JOIN ' . $object->_table_name . ($alias == $object->_table_name ? '' : ' ' . $alias) . ' ON ';
		if ($options['as']) {
			$from .= $alias . '.' . $pk_related . ' = ' . $table_related . '.' . $options['as'] . '_id and ' . $table_related . '.' . $options['as'] . '_type = ?';
			$values[] = $options['source_type'];
		}
		else
			$from .=  $table_related . '.' . $options['foreign_key'] . ' = ' . $alias . '.' . $pk_related;
	}

	# USED INTERNALLY
	# find_belongs_to($relation, $extra_options = array())
	# ====================================================
	#
	# Get the belongs_to object with the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * relation: the relation's identifier on the model.
	# * extra_options: extra options for the relation that will overwrite the setted on the model.
	#
	#
	#
	# Returns
	# -------
	#
	# The founded related object or false none is found.
	final protected function find_belongs_to($relation, $extra_options = array()) {
		if ($this->new_record())
			return false;

		$options = array_merge($this->get_options_for_belongs_to($relation), $extra_options);
		$object = new $options['class_name'];

		if ($options['finder_sql']) {
			$sql = apply_template($options['finder_sql'], $this->{$this->_primary_key});
			$objects = $object->find_by_sql($sql);
			return count($objects) == 0 ? false : array_shift($objects);
		}

		$values = $conditions = array();
		$this->stringify_conditions($options['conditions'], $conditions, $values);

		if ($options['through']) {
			$options_through = $this->get_options_for_belongs_to($options['through']);
			$through_object = new $options_through['class_name'];

			if ($options['source']) $relation = $options['source'];
			$options_relation = $through_object->get_options_for_belongs_to($relation);
			$object_relation = new $options_relation['class_name'];

			if ($options_relation['polymorphic']) {
//				$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $object->_table_name . '.' . $options_relation['as'] . '_id = ? AND ' . $object->_table_name . '.' . $options_relation['as'] . '_type = ?';
//				array_push($values, $this->$options['through']->{$this->$options['through']->_primary_key}, $options_relation['source_type']);
			}
			else
				$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $through_object->_table_name . '.' . $options_relation['foreign_key'] . ' = ' . $object_relation->_table_name . '.' . $object_relation->_primary_key;
			$options['joins'] = $join . ($options['joins'] ? ' ' . $options['joins'] : '');

			if (!$options['select'])
				$options['select'] = $object_relation->_table_name . '.*';

			$options['from'] = $through_object->_table_name;
			$this->stringify_ids_conditions($this->$options_through['foreign_key'], $through_object->_table_name . '.' . $through_object->_primary_key, $conditions, $values);
		}
		elseif ($options['polymorphic']) {
			$conditions[] = $object->_table_name . '.' . $object->_primary_key . ' = ?';
			$values[] = $this->{$options['as'] . '_id'};
		}
		else
			$this->stringify_ids_conditions($this->$options['foreign_key'], $object->_table_name . '.' . $object->_primary_key, $conditions, $values);

		$options['conditions'] = array(implode(' AND ', $conditions), $values);
		return $object->find('first', $options);
	}

	# USED INTERNALLY
	# save_belongs_to()
	# ==============
	#
	# Saves the has one relation objects stored in this model.
	# This method is called automatically on model save.
	final protected function save_belongs_to() {
		foreach ($this->_belongs_to as $relation => $options) {
			if (is_numeric($relation))
				$relation = $options;
			if ($object = $this->find_belongs_to($relation))
				$object->save();
		}
	}

	# USED INTERNALLY
	# replace_belongs_to($key, $object)
	# ==============================
	#
	# Adds the given object to this model as a has one relation.
	# If this object has a has one relation object already it will be removed before the new object addition.
	# The remove operation will be the one specified on the relation options.
	# If this object has been saved the given object is updated or created with the foreign key setted to
	# point to this object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	# * object: the object that will be linked to this model.
	final protected function replace_belongs_to($key, $object) {
		if (!$this->new_record()) {
			$options = $this->get_options_for_belongs_to($key);
			$this->remove_belongs_to($key);
			$this->before_add($object);
			$this->_trigger_observers('before_add');
			if ($object->new_record()) {
				if ($object->save()) {
					$this->$options['foreign_key'] = $object->{$object->_primary_key};
					$this->after_add($object);
					$this->_trigger_observers('after_add');
				}
			}
			elseif ($this->update_attribute($options['foreign_key'], $object->{$object->_primary_key})) {
				$this->after_add($object);
				$this->_trigger_observers('after_add');
			}
		}
		$this->_attributes[$key] = $object;
	}

	# USED INTERNALLY
	# remove_belongs_to($key)
	# ====================
	#
	# Removes the has one given relation on this model.
	# The method for the remove is the specified on the relation options or nullify if none is specified.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function remove_belongs_to($key) {
		$options = $this->get_options_for_belongs_to($key);
		$dependent = $options['dependent'] ? $options['dependent'] : 'nullify';
		$this->{$dependent . '_belongs_to'}($key);
	}

	# USED INTERNALLY
	# nullify_belongs_to($key)
	# =====================
	#
	# Removes the has one given relation on this model updating the foreign key of the relation object and setting
	# it to the default value of the field.
	# The callbacks for the delete operation are not trigged because the nullify is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function nullify_belongs_to($key) {
		$options = $this->get_options_for_belongs_to($key);
		$object = new $options['class_name'];
		if (!$options['through'])
			if ($options['polymorphic'])
				$object->update_by_sql('UPDATE ' . $this->_table_name . ' SET ' . $options['as'] . '_id = ? WHERE ' . $options['as'] . '_id = ? AND ' . $options['as'] . '_type = ?', $this->default_of_attribute($options['as'] . '_id'), $object->{$object->_primary_key}, $key);
			else
				$object->update_by_sql('UPDATE ' . $this->_table_name . ' SET ' . $options['foreign_key'] . ' = ? WHERE ' . $options['foreign_key'] . ' = ?', $this->default_of_attribute($options['foreign_key']), $object->{$object->_primary_key});
		$this->_attributes[$options['foreign_key']] = false;
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# delete_belongs_to($key)
	# ====================
	#
	# Removes the has one given relation on this model deleting the relation object on the database.
	# The callbacks for the delete operation are not trigged because the remove is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function delete_belongs_to($key) {
		$options = $this->get_options_for_belongs_to($key);
		$object = new $options['class_name'];
		if (!$options['through'])
			if ($options['polymorphic'])
				$object->update_by_sql('DELETE FROM ' . $object->_table_name . ' WHERE ' . $object->_primary_key . ' = ?', $this->{$options['as'] . '_id'});
			else
				$object->update_by_sql('DELETE FROM ' . $object->_table_name . ' WHERE ' . $object->_primary_key . ' = ?', $this->$options['foreign_key']);
		$this->_attributes[$options['foreign_key']] = false;
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# destroy_belongs_to($key)
	# =====================
	#
	# Removes the has one given relation on this model deleting the relation object on the database.
	# This is the one method that will trigger the callbacks on the relation object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function destroy_belongs_to($key) {
		$object = $this->find_belongs_to($key);
		if ($object)
			$object->destroy();
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# get_options_for_has_one($key)
	# =============================
	#
	# Get the options for the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# Get the options for the given relation key.
	#
	#
	#
	# Options
	# -------
	#
	# * class_name: specify the class name of the association. Use it only if that name can‘t be inferred
	# from the association name. So has_one "manager" will by default be linked to the Manager class, but
	# if the real class name is Person, you‘ll have to specify it with this option.
	# * foreign_key: specify the foreign key used for the association. By default this is guessed to be
	# the name of this class in lower-case and _id suffixed. So a Person class that makes a has_one
	# association will use person_id as the default foreign_key.
	# * through: Specifies a Join Model through which to perform the query.
	# * as: Specifies a polymorphic relation.
	# * source_type: On a polymorphic relation it specifies the type of the relation that will be searched
	# on the relation table (value on the <table>_type field). By default is the object class name.
	# * source: Specifies the source association name used on through queries on a polymorphic relation.
	# Only use it if the name cannot be inferred from the association.
	# has_one "person", through => "subscriptions" will look for "person" on Subscription relation object,
	# unless a source is given.
	# * dependent: if set to "destroy", the associated object is destroyed when this object is. If set to
	# "delete", the associated object is deleted without calling its destroy method. If set to "nullify"
	# (default), the associated object‘s foreign key is set to NULL.
	# * finder_sql: specify a complete SQL statement to fetch the association. This is a good way to go
	# for complex associations that depend on multiple tables.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to
	# do a join but not include the joined columns.
	# * joins: a complete join SQL fragment that specify the relation with the table. This option makes
	# the foreign_key option unservible.
	# * include: specify second-order associations that should be eager loaded when this object is loaded.
	# * conditions: specify the conditions that the associated object must meet in order to be included
	# as a WHERE SQL fragment, such as rank = 5.
	# * order: specify the order in which the associated objects are returned as an ORDER BY SQL fragment,
	# such as last_name, first_name DESC
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * having: Specify the group conditions that the associated object must meet in order to be included on
	# finds when group option is used.
	# * limit: An integer determining the limit on the number of rows that should be returned.
	# * offset: An integer determining the offset from where the rows should be fetched. So at 5, it would
	# skip the first 4 rows.
	# * lock: An SQL fragment like "FOR UPDATE" or "LOCK IN SHARE MODE".
	#   lock => true gives connection's default exclusive lock, usually "FOR UPDATE".
	#
	#
	#
	# Returns
	# -------
	#
	# * An associative array with all posible options.
	final protected function get_options_for_has_one($key) {
		$lower_class_name = strtolower($this->_class_name);
		$base_options = array('class_name' => Inflector::classify($key), 'foreign_key' => $lower_class_name . '_id', 'through' => false, 'as' => false, 'source' => false, 'source_type' => $lower_class_name, 'dependent' => 'nullify', 'finder_sql' => false, 'select' => false, 'include' => array(), 'joins' => false, 'conditions' => false, 'order' => false, 'group' => false, 'having' => false, 'limit' => false, 'offset' => false, 'lock' => false);
		foreach ($this->_has_one as $relation => $options)
			if ($options == $key)		return $base_options;
			elseif ($relation === $key)	return array_merge($base_options, $options);
	}

	# USED INTERNALLY
	# construct_find_with_has_one($include, $nested_includes, &$select, &$from, &$values, &$include_information)
	# ==========================================================================================================
	#
	# Constructs the sql for a include operation with the has one given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * include: the include relation key.
	# * nested_includes: includes that are nested to the include key object.
	# * select: the select statement that will be completed.
	# * from: the from statement that will be completed.
	# * values: values that will be passed to the database query for prepared statements.
	# * include_information: an array with the include information needed to process the relations on the returned
	# database data.
	final protected function construct_find_with_has_one($include, $nested_includes, &$select, &$from, &$values, &$include_information) {
		$options = $this->get_options_for_has_one($include);
		$object = new $options['class_name'];

		$alias = $object->get_alias($include_information);

		$select .= ', ';
		$object->add_fields_for($alias, $select);

		$include_information[$alias] = array('cn' => $object->_class_name, 'pk' => $object->_primary_key, 'r' => $this->_table_name, 's' => $object->_serialize, 'u' => true, 'v' => $include);

		$this->add_has_one_join($options, $include, $object, $alias, $from, $values, $include_information, $this->_table_name, $this->_primary_key);

		$object->add_includes($nested_includes, $select, $from, $values, $include_information);
	}

	# USED INTERNALLY
	# add_has_one_join($options, $include, $object, $alias, &$from, &$values, &$include_information, $table_related, $pk_related)
	# ===========================================================================================================================
	#
	# Completes the from statement for a include operation.
	protected function add_has_one_join($options, $include, $object, $alias, &$from, &$values, &$include_information, $table_related, $pk_related) {
		if ($options['through']) {
			$options_through = $this->get_options_for_has_one($options['through']);
			$through_object = new $options_through['class_name'];

			$through_alias = $through_object->get_alias($include_information);

			$object->add_has_one_join($options_through, $include, $through_object, $through_alias, $from, $values, $include_information, $table_related, $pk_related);

			$options = $through_object->get_options_for_has_one($include);

			$table_related = $through_alias;
			$pk_related = $through_object->_primary_key;
		}

		$from .= ' LEFT OUTER JOIN ' . $object->_table_name . ($alias == $object->_table_name ? '' : ' ' . $alias) . ' ON ';
		if ($options['as']) {
			$from .= $alias . '.' . $options['as'] . '_id = ' . $table_related . '.' . $pk_related . ' AND ' . $alias . '.' . $options['as'] . '_type = ?';
			$values[] = $options['source_type'];
		}
		else
			$from .=  $table_related . '.' . $pk_related . ' = ' . $alias . '.' . $options['foreign_key'];
	}

	# USED INTERNALLY
	# find_has_one($relation, $extra_options = array())
	# =================================================
	#
	# Get the has_one object with the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * relation: the relation's identifier on the model.
	# * extra_options: extra options for the relation that will overwrite the setted on the model.
	#
	#
	#
	# Returns
	# -------
	#
	# The founded related object or false none is found.
	final protected function find_has_one($relation, $extra_options = array()) {
		if ($this->new_record())
			return false;

		$options = array_merge($this->get_options_for_has_one($relation), $extra_options);
		$object = new $options['class_name'];

		if ($options['finder_sql']) {
			$sql = apply_template($options['finder_sql'], $this->{$this->_primary_key});
			$objects = $object->find_by_sql($sql);
			return count($objects) == 0 ? false : array_shift($objects);
		}

		$values = $conditions = array();
		$this->stringify_conditions($options['conditions'], $conditions, $values);

		if ($options['through']) {
			$options_through = $this->get_options_for_has_one($options['through']);
			$through_object = new $options_through['class_name'];

			if ($options['source']) $relation = $options['source'];
			$options_relation = $through_object->get_options_for_has_one($relation);
			$object_relation = new $options_relation['class_name'];

			if ($options_relation['as']) {
				$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $object->_table_name . '.' . $options_relation['as'] . '_id = ? AND ' . $object->_table_name . '.' . $options_relation['as'] . '_type = ?';
				array_push($values, $this->$options['through']->{$this->$options['through']->_primary_key}, $options_relation['source_type']);
			}
			else
				$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $through_object->_table_name . '.' . $through_object->_primary_key . ' = ' . $object_relation->_table_name . '.' . $options_relation['foreign_key'];
			$options['joins'] = $join . ($options['joins'] ? ' ' . $options['joins'] : '');

			if (!$options['select'])
				$options['select'] = $object_relation->_table_name . '.*';

			$options['from'] = $through_object->_table_name;
			$this->stringify_ids_conditions($this->{$this->_primary_key}, $through_object->_table_name . '.' . $options_through['foreign_key'], $conditions, $values);
		}
		elseif ($options['as']) {
			$conditions[] = $object->_table_name . '.' . $options['as'] . '_id = ? AND ' . $object->_table_name . '.' . $options['as'] . '_type = ?';
			array_push($values, $this->{$this->_primary_key}, $options['source_type']);
		}
		else
			$this->stringify_ids_conditions($this->{$this->_primary_key}, $object->_table_name . '.' . $options['foreign_key'], $conditions, $values);

		$options['conditions'] = array(implode(' AND ', $conditions), $values);
		return $object->find('first', $options);
	}

	# USED INTERNALLY
	# save_has_one()
	# ==============
	#
	# Saves the has one relation objects stored in this model.
	# This method is called automatically on model save.
	final protected function save_has_one() {
		foreach ($this->_has_one as $relation => $options) {
			if (is_numeric($relation))
				$relation = $options;
			if ($object = $this->find_has_one($relation)){
				$object->save();
			}	
		}
	}

	# USED INTERNALLY
	# replace_has_one($key, $object)
	# ==============================
	#
	# Adds the given object to this model as a has one relation.
	# If this object has a has one relation object already it will be removed before the new object addition.
	# The remove operation will be the one specified on the relation options.
	# If this object has been saved the given object is updated or created with the foreign key setted to
	# point to this object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	# * object: the object that will be linked to this model.
	final protected function replace_has_one($key, $object) {
		if (!$this->new_record()) {
			$options = $this->get_options_for_has_one($key);
			$this->remove_has_one($key);
			$this->before_add($object);
			$this->_trigger_observers('before_add');
			if ($object->new_record()) {
				$object->$options['foreign_key'] = $this->{$this->_primary_key};
				if ($object->save()) {
					$this->after_add($object);
					$this->_trigger_observers('after_add');
				}
			}
			elseif ($object->update_attribute($options['foreign_key'], $this->{$this->_primary_key})) {
				$this->after_add($object);
				$object->_trigger_observers('after_add');
			}
		}
		$this->_attributes[$key] = $object;
	}

	# USED INTERNALLY
	# remove_has_one($key)
	# ====================
	#
	# Removes the has one given relation on this model.
	# The method for the remove is the specified on the relation options or nullify if none is specified.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function remove_has_one($key) {
		$options = $this->get_options_for_has_one($key);
		$dependent = $options['dependent'] ? $options['dependent'] : 'nullify';
		$this->{$dependent . '_has_one'}($key);
	}

	# USED INTERNALLY
	# nullify_has_one($key)
	# =====================
	#
	# Removes the has one given relation on this model updating the foreign key of the relation object and setting
	# it to the default value of the field.
	# The callbacks for the delete operation are not trigged because the nullify is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function nullify_has_one($key) {
		$options = $this->get_options_for_has_one($key);
		$object = new $options['class_name'];
		if (!$options['through'] && !$options['finder_sql'])
			if ($options['as'])
				$object->update_by_sql('UPDATE ' . $object->_table_name . ' SET ' . $options['as'] . '_id = ? WHERE ' . $options['as'] . '_id = ? AND ' . $options['as'] . '_type = ?', $object->default_of_attribute($options['as'] . '_id'), $this->{$this->_primary_key}, $options['source_type']);
			else
				$object->update_by_sql('UPDATE ' . $object->_table_name . ' SET ' . $options['foreign_key'] . ' = ? WHERE ' . $options['foreign_key'] . ' = ?', $object->default_of_attribute($options['foreign_key']), $this->{$this->_primary_key});
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# delete_has_one($key)
	# ====================
	#
	# Removes the has one given relation on this model deleting the relation object on the database.
	# The callbacks for the delete operation are not trigged because the remove is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function delete_has_one($key) {
		$options = $this->get_options_for_has_one($key);
		$object = new $options['class_name'];
		if (!$options['through'] && !$options['finder_sql'])
			if ($options['as'])
				$object->delete_by_sql('DELETE ' . $object->_table_name . ' WHERE ' . $options['as'] . '_id = ? AND ' . $options['as'] . '_type = ?', $this->{$this->_primary_key}, $options['source_type']);
			else
				$object->delete_by_sql('DELETE FROM ' . $object->_table_name . ' WHERE ' . $options['foreign_key'] . ' = ?', $this->{$this->_primary_key});
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# destroy_has_one($key)
	# =====================
	#
	# Removes the has one given relation on this model deleting the relation object on the database.
	# This is the one method that will trigger the callbacks on the relation object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function destroy_has_one($key) {
		$object = $this->find_has_one($key);
		if ($object)
			$object->destroy();
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# get_options_for_has_many($key)
	# ==============================
	#
	# Get the options for the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# Get the options for the given relation key.
	#
	#
	#
	# Options
	# -------
	#
	# * class_name: specify the class name of the association. Use it only if that name can‘t be inferred
	# from the association name. So has_many "managers" will by default be linked to the Manager class, but
	# if the real class name is Person, you‘ll have to specify it with this option.
	# * foreign_key: specify the foreign key used for the association. By default this is guessed to be
	# the name of this class in lower-case and _id suffixed. So a Person class that makes a has_many
	# association will use person_id as the default foreign_key.
	# * through: Specifies a Join Model through which to perform the query.
	# * as: Specifies a polymorphic relation.
	# * source_type: On a polymorphic relation it specifies the type of the relation that will be searched
	# on the relation table (value on the <table>_type field). By default is the object class name.
	# * source: Specifies the source association name used on through queries on a polymorphic relation.
	# Only use it if the name cannot be inferred from the association.
	# has_many "people", through => "subscriptions" will look for "person" on Subscription relation object,
	# unless a source is given.
	# * dependent: if set to "destroy", the associated object is destroyed when this object is. If set to
	# "delete", the associated object is deleted without calling its destroy method. If set to "nullify"
	# (default), the associated object‘s foreign key is set to NULL.
	# * finder_sql: specify a complete SQL statement to fetch the association. This is a good way to go
	# for complex associations that depend on multiple tables.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to
	# do a join but not include the joined columns.
	# * joins: a complete join SQL fragment that specify the relation with the table. This option makes
	# the foreign_key option unservible.
	# * include: specify second-order associations that should be eager loaded when this object is loaded.
	# * conditions: specify the conditions that the associated object must meet in order to be included
	# as a WHERE SQL fragment, such as rank = 5.
	# * order: specify the order in which the associated objects are returned as an ORDER BY SQL fragment,
	# such as last_name, first_name DESC
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * having: Specify the group conditions that the associated object must meet in order to be included on
	# finds when group option is used.
	# * limit: An integer determining the limit on the number of rows that should be returned.
	# * offset: An integer determining the offset from where the rows should be fetched. So at 5, it would
	# skip the first 4 rows.
	# * lock: An SQL fragment like "FOR UPDATE" or "LOCK IN SHARE MODE".
	#   lock => true gives connection's default exclusive lock, usually "FOR UPDATE".
	#
	#
	#
	# Returns
	# -------
	#
	# * An associative array with all posible options.
	final protected function get_options_for_has_many($key) {
		$lower_class_name = strtolower($this->_class_name);
		$base_options = array('class_name' => Inflector::classify($key), 'foreign_key' => $lower_class_name . '_id', 'through' => false, 'as' => false, 'source' => false, 'source_type' => $lower_class_name, 'dependent' => 'nullify', 'finder_sql' => false, 'select' => false, 'include' => array(), 'joins' => false, 'conditions' => false, 'order' => false, 'group' => false, 'having' => false, 'limit' => false, 'offset' => false, 'lock' => false);
		foreach ($this->_has_many as $relation => $options)
			if ($options == $key)		return $base_options;
			elseif ($relation === $key)	return array_merge($base_options, $options);
	}

	# USED INTERNALLY
	# construct_find_with_has_many($include, $nested_includes, &$select, &$from, &$values, &$include_information)
	# ===========================================================================================================
	#
	# Constructs the sql for a include operation with the has many given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * include: the include relation key.
	# * nested_includes: includes that are nested to the include key object.
	# * select: the select statement that will be completed.
	# * from: the from statement that will be completed.
	# * values: values that will be passed to the database query for prepared statements.
	# * include_information: an array with the include information needed to process the relations on the returned
	# database data.
	final protected function construct_find_with_has_many($include, $nested_includes, &$select, &$from, &$values, &$include_information) {
		$options = $this->get_options_for_has_many($include);
		$object = new $options['class_name'];

		$alias = $object->get_alias($include_information);

		$select .= ', ';
		$object->add_fields_for($alias, $select);

		$include_information[$alias] = array('cn' => $object->_class_name, 'pk' => $object->_primary_key, 'r' => $this->_table_name, 's' => $object->_serialize, 'u' => false, 'v' => $include);

		$this->add_has_many_join($options, $include, $object, $alias, $from, $values, $include_information, $this->_table_name, $this->_primary_key);

		$object->add_includes($nested_includes, $select, $from, $values, $include_information);
	}

	# USED INTERNALLY
	# add_has_many_join
	# ================
	#
	# Completes the from statement for a include operation.
	protected function add_has_many_join($options, $include, $object, $alias, &$from, &$values, &$include_information, $table_related, $pk_related) {
		if ($options['through']) {
			$options_through = $this->get_options_for_has_many($options['through']);
			$through_object = new $options_through['class_name'];

			$through_alias = $through_object->get_alias($include_information);

			$object->add_has_many_join($options_through, $include, $through_object, $through_alias, $from, $values, $include_information, $table_related, $pk_related);

			$options = $through_object->get_options_for_has_many($include);

			$table_related = $through_alias;
			$pk_related = $through_object->_primary_key;
		}

		$from .= ' LEFT OUTER JOIN ' . $object->_table_name . ($alias == $object->_table_name ? '' : ' ' . $alias) . ' ON ';
		if ($options['as']) {
			$from .= $alias . '.' . $options['as'] . '_id = ' . $table_related . '.' . $pk_related . ' AND ' . $alias . '.' . $options['as'] . '_type = ?';
			$values[] = $options['source_type'];
		}
		else
			$from .=  $table_related . '.' . $pk_related . ' = ' . $alias . '.' . $options['foreign_key'];
	}

	# USED INTERNALLY
	# find_has_many($relation, $extra_options = array())
	# ==================================================
	#
	# Get the has_many object with the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * relation: the relation's identifier on the model.
	# * extra_options: extra options for the relation that will overwrite the setted on the model.
	#
	#
	#
	# Returns
	# -------
	#
	# The founded related object or false none is found.
	final protected function find_has_many($relation, $extra_options = array()) {
		if ($this->new_record())
			return false;

		$options = array_merge($this->get_options_for_has_many($relation), $extra_options);
		$object = new $options['class_name'];

		if ($options['finder_sql']) {
			$sql = apply_template($options['finder_sql'], $this->{$this->_primary_key});
			return $object->find_by_sql($sql);
		}

		$values = $conditions = array();
		$this->stringify_conditions($options['conditions'], $conditions, $values);

		if ($options['through']) {
			$options_through = $this->get_options_for_has_many($options['through']);
			$through_object = new $options_through['class_name'];

			if ($options['source']) $relation = $options['source'];
			$options_relation = $through_object->get_options_for_has_many($relation);
			$object_relation = new $options_relation['class_name'];

			if ($options_relation['as']) {
				$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $object->_table_name . '.' . $options_relation['as'] . '_id = ? AND ' . $object->_table_name . '.' . $options_relation['as'] . '_type = ?';
				array_push($values, $this->$options['through']->{$this->$options['through']->_primary_key}, $options_relation['source_type']);
			}
			else
				$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $through_object->_table_name . '.' . $through_object->_primary_key . ' = ' . $object_relation->_table_name . '.' . $options_relation['foreign_key'];
			$options['joins'] = $join . ($options['joins'] ? ' ' . $options['joins'] : '');

			if (!$options['select'])
				$options['select'] = $object_relation->_table_name . '.*';

			$options['from'] = $through_object->_table_name;
			$this->stringify_ids_conditions($this->{$this->_primary_key}, $through_object->_table_name . '.' . $options_through['foreign_key'], $conditions, $values);
		}
		elseif ($options['as']) {
			$conditions[] = $object->_table_name . '.' . $options['as'] . '_id = ? AND ' . $object->_table_name . '.' . $options['as'] . '_type = ?';
			array_push($values, $this->{$this->_primary_key}, $options['source_type']);
		}
		else
			$this->stringify_ids_conditions($this->{$this->_primary_key}, $object->_table_name . '.' . $options['foreign_key'], $conditions, $values);

		$options['conditions'] = array(implode(' AND ', $conditions), $values);

		return $object->find('all', $options);
	}

	# USED INTERNALLY
	# save_has_many
	# ============
	#
	# Saves the has many relation objects stored in this model.
	# This method is called automatically on model save.
	final protected function save_has_many() {
		foreach ($this->_has_many as $relation => $options) {
			if (is_numeric($relation))
				$relation = $options;
			if (isset($this->_attributes[$relation]))
				$this->add_has_many($relation, $this->_attributes[$relation]);
		}
	}

	# USED INTERNALLY
	# replace_has_many($key, $objects)
	# ================================
	#
	# Adds the given object/s to this model as a has many relation.
	# If this object has a has many relation object already it will be removed before the new object addition.
	# The remove operation will be the one specified on the relation options.
	# If this object has been saved the given objects are updated or created with the foreign key setted to
	# point to this object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	# * objects: an array of objects or a single object that will be linked to this model.
	final protected function replace_has_many($key, $objects) {
		if ($this->new_record()) {
			$this->_attributes[$key] = $objects;

			return;
		}

		if (is_object($objects))
			$objects = array($objects);

		$this->remove_has_many($key);

		$this->add_has_many_objects($key, $objects);
	}

	# USED INTERNALLY
	# add_has_many($key, $objects)
	# ============================
	#
	# Adds the given object/s to this model as a has many relation.
	# If this object has been saved the given objects are updated or created with the foreign key setted to
	# point to this object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	# * objects: an array of objects or a single object that will be linked to this model.
	final protected function add_has_many($key, $objects) {
		if ($this->new_record())
			return;

		if (is_object($objects))
			$objects = array($objects);

		$this->add_has_many_objects($key, $objects);
	}

	# USED INTERNALLY
	# add_has_many_objects($key, $objects)
	# ====================================
	#
	# Common method to add objects to has many relation.
	final private function add_has_many_objects($key, $objects) {
		$options = $this->get_options_for_has_many($key);

		foreach ($objects as $object)
			if ($object->new_record()) {
				$object->$options['foreign_key'] = $this->{$this->_primary_key};
				$object->save();
			}
			else
				$object->update_attribute($options['foreign_key'], $this->{$this->_primary_key});

		$this->_attributes[$key] = $objects;
	}

	# USED INTERNALLY
	# remove_has_many($key)
	# =====================
	#
	# Removes the has many given relation on this model.
	# The method for the remove is the specified on the relation options or nullify if none is specified.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function remove_has_many($key) {
		$options = $this->get_options_for_has_many($key);
		$dependent = $options['dependent'] ? $options['dependent'] : 'nullify';
		$this->{$dependent . '_has_many'}($key);
	}

	# USED INTERNALLY
	# nullify_has_many($key)
	# ======================
	#
	# Removes the has many given relation on this model updating the foreign key of the relation objects and setting
	# it to the default value of the field.
	# The callbacks for the delete operation are not trigged because the nullify is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function nullify_has_many($key) {
		$options = $this->get_options_for_has_many($key);
		$object = new $options['class_name'];
		$object->update_by_sql('UPDATE ' . $object->_table_name . ' SET ' . $options['foreign_key'] . ' = ? WHERE ' . $options['foreign_key'] . ' = ?', $object->default_of_attribute($options['foreign_key']), $this->{$this->_primary_key});
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# delete_has_many($key)
	# =====================
	#
	# Removes the has many given relation on this model deleting the relation object on the database.
	# The callbacks for the delete operation are not trigged because the remove is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function delete_has_many($key) {
		$options = $this->get_options_for_has_many($key);
		$object = new $options['class_name'];
		$object->delete_by_sql('DELETE FROM ' . $object->_table_name . ' WHERE ' . $options['foreign_key'] . ' = ?', $this->{$this->_primary_key});
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# destroy_has_many($key)
	# ======================
	#
	# Removes the has many given relation on this model deleting the relation object on the database.
	# This is the one method that will trigger the callbacks on the relation object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function destroy_has_many($key) {
		foreach ($this->find_has_many($key) as $object)
			$object->destroy();
		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# get_options_for_has_and_belongs_to_many($key)
	# =============================================
	#
	# Get the options for the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# Get the options for the given relation key.
	#
	#
	#
	# Options
	# -------
	#
	# * class_name: specify the class name of the association. Use it only if that name canët be inferred
	# from the association name. So has_and_belongs_to_many "managers" will by default be linked to the Manager class, but
	# if the real class name is Person, youëll have to specify it with this option.
	# * foreign_key: specify the foreign key used for the association. By default this is guessed to be
	# the name of this class in lower-case and _id suffixed. So a Person class that makes a has_and_belongs_to_many
	# association will use person_id as the default foreign_key.
	# * :association_foreign_key: Specify the foreign key used for the association on the receiving side of the association.
	# By default this is guessed to be the name of the associated class in lower-case and "_id" suffixed. So if a Person
	# class makes a has_and_belongs_to_many association to Project, the association will use "project_id" as the default
	# association_foreign_key. 
	# join_table: Specify the name of the join table if the default based on lexical order isnët what you want.
	# * through: Specifies a Join Model through which to perform the query.
	# * dependent: if set to "destroy", the associated object is destroyed when this object is. If set to
	# "delete", the associated object is deleted without calling its destroy method. If set to "nullify"
	# (default), the associated objectës foreign key is set to NULL.
	# * finder_sql: specify a complete SQL statement to fetch the association. This is a good way to go
	# for complex associations that depend on multiple tables.
	# * select: By default, this is * as in SELECT * FROM, but can be changed if you, for example, want to
	# do a join but not include the joined columns.
	# * joins: a complete join SQL fragment that specify the relation with the table. This option makes
	# the foreign_key option unservible.
	# * include: specify second-order associations that should be eager loaded when this object is loaded.
	# * conditions: specify the conditions that the associated object must meet in order to be included
	# as a WHERE SQL fragment, such as rank = 5.
	# * order: specify the order in which the associated objects are returned as an ORDER BY SQL fragment,
	# such as last_name, first_name DESC
	# * group: An attribute name by which the result should be grouped. Uses the GROUP BY SQL-clause.
	# * having: Specify the group conditions that the associated object must meet in order to be included on
	# finds when group option is used.
	# * limit: An integer determining the limit on the number of rows that should be returned.
	# * offset: An integer determining the offset from where the rows should be fetched. So at 5, it would
	# skip the first 4 rows.
	# * lock: An SQL fragment like "FOR UPDATE" or "LOCK IN SHARE MODE".
	#   lock => true gives connection's default exclusive lock, usually "FOR UPDATE".
	#
	#
	#
	# Returns
	# -------
	#
	# * An associative array with all posible options.
	final protected function get_options_for_has_and_belongs_to_many($key) {
		$class = Inflector::classify($key);
		$associated_object = new $class;

		$base_options = array(
			'foreign_key' => strtolower($this->_class_name) . '_id',
			'association_foreign_key' => strtolower($class) . '_id',
			'class_name' => $associated_object->_class_name,
			'join_table' => $associated_object->_table_name > $this->_table_name ? $this->_table_name . '_' . $associated_object->_table_name : $associated_object->_table_name . '_' . $this->_table_name,
			'through' => false,
			'dependent' => 'nullify',
			'finder_sql' => false,
			'select' => false,
			'include' => array(),
			'joins' => false,
			'conditions' => false,
			'order' => false,
			'group' => false,
			'having' => false,
			'limit' => false,
			'offset' => false,
			'lock' => false
		);

		foreach ($this->_has_and_belongs_to_many as $relation => $options)
			if ($options == $key)
				return $base_options;
			elseif ($relation === $key)
				return array_merge($base_options, $options);
	}

	# USED INTERNALLY
	# construct_find_with_has_and_belongs_to_many($include, $nested_includes, &$select, &$from, &$values, &$include_information)
	# ==========================================================================================================================
	#
	# Constructs the sql for a include operation with the has and belongs to many given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * include: the include relation key.
	# * nested_includes: includes that are nested to the include key object.
	# * select: the select statement that will be completed.
	# * from: the from statement that will be completed.
	# * values: values that will be passed to the database query for prepared statements.
	# * include_information: an array with the include information needed to process the relations on the returned
	# database data.
	final protected function construct_find_with_has_and_belongs_to_many($include, $nested_includes, &$select, &$from, &$values, &$include_information) {
		$options = $this->get_options_for_has_and_belongs_to_many($include);
		$object = new $options['class_name'];

		$alias = $object->get_alias($include_information);

		$select .= ', ';
		$object->add_fields_for($alias, $select);

		$include_information[$alias] = array('cn' => $object->_class_name, 'pk' => $object->_primary_key, 'r' => $this->_table_name, 's' => $object->_serialize, 'u' => false, 'v' => $include);

		$this->add_has_and_belongs_to_many_join($options, $include, $object, $alias, $from, $values, $include_information, $this->_table_name, $this->_primary_key);

		$object->add_includes($nested_includes, $select, $from, $values, $include_information);
	}

	# USED INTERNALLY
	# add_has_and_belongs_to_many_join
	# ================================
	#
	# Completes the from statement for a include operation.
	protected function add_has_and_belongs_to_many_join($options, $include, $object, $alias, &$from, &$values, &$include_information, $table_related, $pk_related) {
		/*
		if ($options['through']) {
			$options_through = $this->get_options_for_has_and_belongs_to_many($options['through']);
			$through_object = new $options_through['class_name'];

			$through_alias = $through_object->get_alias($include_information);

			$object->add_has_and_belongs_to_many_join($options_through, $include, $through_object, $through_alias, $from, $values, $include_information, $table_related, $pk_related);

			$options = $through_object->get_options_for_has_and_belongs_to_many($include);

			$table_related = $through_alias;
			$pk_related = $through_object->_primary_key;
		}

		$from .= ' LEFT OUTER JOIN ' . $object->_table_name . ($alias == $object->_table_name ? '' : ' ' . $alias) . ' ON ';
		$from .=  $table_related . '.' . $pk_related . ' = ' . $alias . '.' . $options['foreign_key'];
		*/
	}

	# USED INTERNALLY
	# find_has_and_belongs_to_many($relation, $extra_options = array())
	# =================================================================
	#
	# Get the has_and_belongs_to_many object with the given relation.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * relation: the relation's identifier on the model.
	# * extra_options: extra options for the relation that will overwrite the setted on the model.
	#
	#
	#
	# Returns
	# -------
	#
	# The founded related object or false none is found.
	final protected function find_has_and_belongs_to_many($relation, $extra_options = array()) {
		if ($this->new_record())
			return false;

		$options = array_merge($this->get_options_for_has_and_belongs_to_many($relation), $extra_options);
		$object = new $options['class_name'];

		if ($options['finder_sql']) {
			$sql = apply_template($options['finder_sql'], $this->{$this->_primary_key});
			return $object->find_by_sql($sql);
		}

		$values = $conditions = array();
		$this->stringify_conditions($options['conditions'], $conditions, $values);

		if ($options['through']) {
/*
			$options_through = $this->get_options_for_has_and_belongs_to_many($options['through']);
			$through_object = new $options_through['class_name'];

			$options_relation = $through_object->get_options_for_has_and_belongs_to_many($relation);
			$object_relation = new $options_relation['class_name'];

			$join = 'LEFT OUTER JOIN ' . $object_relation->_table_name . ' ON ' . $through_object->_table_name . '.' . $through_object->_primary_key . ' = ' . $object_relation->_table_name . '.' . $options_relation['foreign_key'];
			$options['joins'] = $join . ($options['joins'] ? ' ' . $options['joins'] : '');

			if (!$options['select'])
				$options['select'] = $object_relation->_table_name . '.*';

			$options['from'] = $through_object->_table_name;
			$this->stringify_ids_conditions($this->{$this->_primary_key}, $through_object->_table_name . '.' . $options_through['foreign_key'], $conditions, $values);
*/
		}
		else {
			$options['joins'] = ($options['joins'] ? ' ' : '') . 'INNER JOIN ' . $options['join_table'] . ' on ' . $object->_table_name . '.' . $object->_primary_key . ' = ' . $options['join_table'] . '.' . $options['association_foreign_key'];

			$this->stringify_ids_conditions($this->{$this->_primary_key}, $options['join_table'] . '.' . $options['foreign_key'], $conditions, $values);
		}

		$options['conditions'] = array(implode(' AND ', $conditions), $values);

		return $object->find('all', $options);
	}

	# USED INTERNALLY
	# save_has_and_belongs_to_many
	# ============
	#
	# Saves the has and belongs to many relation objects stored in this model.
	# This method is called automatically on model save.
	final protected function save_has_and_belongs_to_many() {
		foreach ($this->_has_and_belongs_to_many as $relation => $options) {
			if (is_numeric($relation))
				$relation = $options;

			if (isset($this->_attributes[$relation]))
				$this->add_has_and_belongs_to_many($relation, $this->_attributes[$relation]);
		}
	}

	# USED INTERNALLY
	# replace_has_and_belongs_to_many($key, $objects)
	# ================================
	#
	# Adds the given object/s to this model as a has and belongs to many relation.
	# If this object has a has and belongs to many relation object already it will be removed before the new object addition.
	# The remove operation will be the one specified on the relation options.
	# If this object has been saved the given objects are updated or created with the foreign key setted to
	# point to this object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	# * objects: an array of objects or a single object that will be linked to this model.
	final protected function replace_has_and_belongs_to_many($key, $objects) {
		if ($this->new_record()) {
			$this->_attributes[$key] = $objects;

			return;
		}

		$this->remove_has_and_belongs_to_many($key);

		$this->add_has_and_belongs_to_many_objects($key, $objects);		

	}

	# USED INTERNALLY
	# add_has_and_belongs_to_many($key, $objects)
	# ============================
	#
	# Adds the given object/s to this model as a has and belongs to many relation.
	# If this object has been saved the given objects are updated or created with the foreign key setted to
	# point to this object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	# * objects: an array of objects or a single object that will be linked to this model.
	final protected function add_has_and_belongs_to_many($key, $objects) {
		if ($this->new_record())
			return;

		$this->add_has_and_belongs_to_many_objects($key, $objects);
	}

	# USED INTERNALLY
	# add_has_and_belongs_to_many_objects($key, $objects)
	# ===================================================
	#
	# Common method to add objects to has and belongs to many relation.
	final private function add_has_and_belongs_to_many_objects($key, $objects) {
		if (is_object($objects))
			$objects = array($objects);

		$options = $this->get_options_for_has_and_belongs_to_many($key);

		foreach ($objects as $object) {
			if ($object->new_record())
				$object->save();

			if (!$object->new_record())
				$object->_connection->query('INSERT INTO ' . $options['join_table'] . ' (' . $options['association_foreign_key'] . ', ' . $options['foreign_key'] . ') values (?, ?)', array($object->{$object->_primary_key}, $this->{$this->_primary_key}));
		}

		$this->_attributes[$key] = $objects;
	}

	# USED INTERNALLY
	# remove_has_and_belongs_to_many($key)
	# =====================
	#
	# Removes the has and belongs to many given relation on this model.
	# The method for the remove is the specified on the relation options or nullify if none is specified.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function remove_has_and_belongs_to_many($key) {
		$options = $this->get_options_for_has_and_belongs_to_many($key);
		$dependent = $options['dependent'] ? $options['dependent'] : 'nullify';
		$this->{$dependent . '_has_and_belongs_to_many'}($key);
	}

	# USED INTERNALLY
	# nullify_has_and_belongs_to_many($key)
	# ======================
	#
	# Removes the has and belongs to many given relation on this model updating the foreign key of the relation objects and setting
	# it to the default value of the field.
	# The callbacks for the delete operation are not trigged because the nullify is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function nullify_has_and_belongs_to_many($key) {
		$options = $this->get_options_for_has_and_belongs_to_many($key);

		$object = new $options['class_name'];
		$object->delete_by_sql('DELETE FROM ' . $options['join_table'] . ' WHERE ' . $options['foreign_key'] . ' = ?', array($this->{$this->_primary_key}));

		unset($this->_attributes[$key]);
	}

	# USED INTERNALLY
	# delete_has_and_belongs_to_many($key)
	# =====================
	#
	# Removes the has and belongs to many given relation on this model deleting the relation object on the database.
	# The callbacks for the delete operation are not trigged because the remove is done with a direct SQL query
	# to the database.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function delete_has_and_belongs_to_many($key) {
		$options = $this->get_options_for_has_and_belongs_to_many($key);

		$object = new $options['class_name'];
		$object->delete_by_sql('DELETE FROM ' . $object->_table_name . ' WHERE ' . $object->_primary_key . ' IN (SELECT ' . $options['association_foreign_key'] . ' FROM ' . $options['join_table'] . ' WHERE ' . $options['foreign_key'] . ' = ?)', $this->{$this->_primary_key});

		unset($this->_attributes[$key]);

		$this->nullify_has_and_belongs_to_many($key);
	}

	# USED INTERNALLY
	# destroy_has_and_belongs_to_many($key)
	# ======================
	#
	# Removes the has and belongs to many given relation on this model deleting the relation object on the database.
	# This is the one method that will trigger the callbacks on the relation object.
	#
	#
	#
	# Arguments
	# ---------
	#
	# * key: the relation's identifier on the model.
	final protected function destroy_has_and_belongs_to_many($key) {
		foreach ($this->find_has_and_belongs_to_many($key) as $object)
			$object->destroy();
		unset($this->_attributes[$key]);

		$this->nullify_has_and_belongs_to_many($key);
	}

	private $_observers = array();

	# USED INTERNALLY
	# _trigger_observers($event)
	# ==========================
	#
	# Triggers the observed events (before_find, after_find, before_save, after_save,
	# before_create, after_create, before_update, after_update, before_delete, after_delete,
	# before_add, after_add, before_remove, after_remove).
	#
	#
	#
	# Arguments
	# ---------
	#
	# * event: the event that triggers the observer.
	final protected function _trigger_observers($event) {
		foreach ($this->_observers as $observer) {
			$ev = array_shift($observer);
			if ($ev == $event) {
				$fn = array_shift($observer);
				call_user_func_array($fn, $observer);
			}
		}
	}

	# add_observer($event, $fn)
	# =========================
	#
	# Adds an observer to one of the object events (before_find, after_find, before_save,
	# after_save, before_create, after_create, before_update, after_update, before_delete,
	# after_delete, before_add, after_add, before_remove, after_remove).
	#
	#
	#
	# Arguments
	# ---------
	#
	# * event: the event that will trigger the observer.
	# * fn: the function to trigger with the observer. Is it posible to assign an array
	# with $object and $method values to trigger a method class.
	#
	#
	#
	# Returns
	# -------
	#
	# * self.
	#
	#
	#
	# Examples
	# --------
	#
	#	$user->add_observer('before_save', 'my_function')
	#
	#	$user->add_observer('after_create', array($contact, 'my_method'))
	#
	#	$user->add_observer('before_delete', array($address, 'update_attribute'), 'city', 'London')
	final public function add_observer() {
		$this->_observers[] = func_get_args();
		return $this;
	}

	# before_find
	# ===========
	#
	# Callback to execute before the find method executes the query on the database.
	protected function before_find() {

	}

	# after_find
	# ===========
	#
	# Callback to execute after the find method executes the query.
	protected function after_find() {

	}

	# before_delete
	# ===========
	#
	# Callback to execute before a delete is produced.
	protected function before_delete() {

	}

	# after_delete
	# ===========
	#
	# Callback to execute after a delete is produced.
	protected function after_delete() {

	}

	# before_update
	# ===========
	#
	# Callback to execute before an update.
	protected function before_update() {

	}

	# after_update
	# ===========
	#
	# Callback to execute after an update.
	protected function after_update() {

	}

	# before_create
	# ===========
	#
	# Callback to execute before a new object is saved.
	protected function before_create() {

	}

	# after_create
	# ===========
	#
	# Callback to execute after a new object is saved.
	protected function after_create() {

	}

	# before_save
	# ===========
	#
	# Callback to execute before a save action (update or insert).
	protected function before_save() {

	}

	# after_save
	# ===========
	#
	# Callback to execute after a save action (update or insert).
	protected function after_save() {

	}

	# before_add
	# ==========
	#
	# Callback to execute before an object is assigned to the object's collection
	protected function before_add() {

	}

	# after_add
	# ==========
	#
	# Callback to execute after an object is assigned to the object's collection
	protected function after_add() {

	}

	# before_remove
	# =============
	#
	# Callback to execute before an object is removed from the object's collection
	protected function before_remove() {

	}

	# after_remove
	# ============
	#
	# Callback to execute after an object is removed from the object's collection
	protected function after_remove() {

	}

}

?>