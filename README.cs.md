Nette Security extension
========================

Rozšíření do Nette poskytující rámec pro ACL. Je nutné naimplementovat jen službu poskytující persistenci práv.
Architektura, kdy neřešíme role, ale jen oprávnění. Každý uživatel má sumu oprávnění na každý zdroj. Pokud
nemá tak nemá přístup.

Práva jsou definována jako trojice oddělená dvojtečkou:
- Resource: např: Post, User, ...
- Operace: např: create, edit, remove, show, showDetail, showDetailName
- Podmínka: any, (author = $sessionUser and editor = Null)


## MacroSet do Latte

Poskytuje makra ověřující, zda právě přihlášený uživatel má nějaké právo. Například:

	<p n:allowed="Post:show:any">...

	{allowed Post:show:any}

	{allowed Post:show:(author = $sessionUser and editor = Null)}

	{allowed Post:show:any}
		...
	{allowedelse}
		...
	{/}


## Traita implementující checkRequirements()

Má na starost zpracovávání anotací @allowed-skip, @allowed(Post:remove:any), @allowed(Post:remove:(author = $sessionUser and editor = Null))

	/**
	 * @allowed(Post:remove:(author = $sessionUser and editor = Null))
	 */
	actionRemove()

or

	#[Allowed('Post:remove:(author = $sessionUser and editor = Null)')]
	actionRemove()


## Panel do Tracy

Možnost zobrazení seznamu práv, který ve výsledku má aktuálně přihlášený uživatel.


## Konfigurace

	services:
		authenticator: App\Model\SecuritiesProviderFromAnything # aka SecuritiesProviderFromNeon

	extensions:
		secur: Taco\NetteSecurity\Extension
