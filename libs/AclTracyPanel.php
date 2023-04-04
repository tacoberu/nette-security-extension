<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\NetteSecurity;

use Tracy;
use Nette;
use Nette\Security\User as SecurityUser;


/**
 * Zobrazí seznam právě přihlášeného uživatele do panelu.
 */
class AclTracyPanel implements Tracy\IBarPanel
{

	private PermissionsProvider $permissions;
	private SecurityUser $user;


	function __construct(
		PermissionsProvider $permissions,
		SecurityUser $user
	) {
		$this->permissions = $permissions;
		$this->user = $user;
	}



	function getTab()
	{
		return '
<span>
	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"><g color="#bebebe" fill="#474747">
		<path d="M2 1v7c0 2.072 1.498 3.695 2.832 4.889a18.66 18.66 0 002.66 1.972l.516.305.512-.31s1.32-.8 2.65-2.002C12.5 11.65 14 10.044 14 8V1zm2 2h8v5c0 .92-1 2.313-2.17 3.37-.913.825-1.477 1.154-1.836 1.386-.358-.226-.918-.543-1.828-1.358C5 10.355 4 8.98 4 8z" style="line-height:normal;font-variant-ligatures:normal;font-variant-position:normal;font-variant-caps:normal;font-variant-numeric:normal;font-variant-alternates:normal;font-feature-settings:normal;text-indent:0;text-align:start;text-decoration-line:none;text-decoration-style:solid;text-decoration-color:#000;text-transform:none;text-orientation:mixed;shape-padding:0;isolation:auto;mix-blend-mode:normal;marker:none" font-weight="400" font-family="sans-serif" overflow="visible"/><path d="M8 4v7.5c-.42-.294-.581-.355-1.156-.875C5.755 9.641 5 8.357 5 8V4z"
			style="marker:none" overflow="visible"/></g></svg>
	<span class="tracy-label">ACL</span>
</span>
';
	}



	function getPanel()
	{
		$id = uniqid('panel-acl');
		$rows = [];
		foreach ($this->permissions->getAllPermissions($this->user) as $perm => $label) {
			$rows[] = "<dt style='padding: .2em .5em; border: 1px solid #E6DFBF; border-bottom: 0 none; font-size: 8pt; color: blue;'>{$perm}</dt>"
					. "<dd style='padding: .2em .5em'>{$label}</dd>";
		}
		return '
<h1>Access List Control</h1>
<input id="' . $id . '"/>
<div class="tracy-inner">
	<div class="tracy-inner-container">
		<dl style="background-color: #FDF5CE; border-bottom: 1px solid #E6DFBF">
		' . implode("\n", $rows) . '
		</dl>
	</div>
</div>

<script>
	(function() {
		var input = document.getElementById("' . $id . '");
		var container = input.parentElement.querySelectorAll("dt");
		input.addEventListener("input", function() {
			for (let i = 0; i < container.length; i++) {
				// Neodpovídající podmínce schováme
				if (container[i].textContent.toLocaleLowerCase().indexOf(input.value.toLocaleLowerCase()) < 0) {
					container[i].style.display = "none";
					container[i].nextElementSibling.style.display = "none";
				}
				else {
					container[i].style.display = null;
					container[i].nextElementSibling.style.display = null;
				}
			}
		});
	})();
</script>
';
	}

}
