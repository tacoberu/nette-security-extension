Nette Security extension
========================

An extension for Nette providing a framework for ACL. It is necessary to implement only a service providing persistence of rights.
An architecture where we don't deal with roles, but only permissions. Each user has a sum of permissions on each resource.
If doesn't have so doesn't have access.

Rights are defined as triplets separated by colons:
- Resource: e.g.: Post, User, ...
- Operation: e.g.: create, edit, remove, show, showDetail, showDetailName
- Condition: e.g.: any, (author = $sessionUser and editor = Null)


## MacroSet for Latte

Provides macros to check if the currently logged-in user has a specific right. For example:

	<p n:allowed="Post:show:any">...

	{allowed Post:show:any}

	{allowed Post:show:(author = $sessionUser and editor = Null)}

	{allowed Post:show:any}
		...
	{allowedelse}
		...
	{/}


## Trait implementing checkRequirements()

Handles the processing of annotations such as @allowed-skip, @allowed(Post:remove:any), @allowed(Post:remove:(author = $sessionUser and editor = Null))

	/**
	 * @allowed(Post:remove:(author = $sessionUser and editor = Null))
	 */
	actionRemove()

or

	#[Allowed('Post:remove:(author = $sessionUser and editor = Null)')]
	actionRemove()


## Tracy Panel

Option to display a list of rights, which the currently logged-in user has.


## Configuration

	services:
		authenticator: App\Model\SecuritiesProviderFromAnything # aka SecuritiesProviderFromNeon

	extensions:
		secur: Taco\NetteSecurity\Extension
