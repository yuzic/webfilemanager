<?php

/**
 * EModificationBehavior
 * Extends CBehavior to automatically save the time at which the model was modified
 * @author Rupert, Radis, 2013
 * @copyright Rupert, 2013
 * @licence http://www.opensource.org/licenses/mit-license.php MIT License
 */
class EModificationBehavior extends CBehavior {
	/**
	 * @var string the name of the attribute to contain the modification timestamp
	 */
	protected $modificationAttribute = 'modified';

	/**
	 * Overridden to attach this behaviour to the beforeSave event
	 * @see CBehavior::events()
	 */
	public function events() {
		return array_merge(parent::events(), array(
			'onBeforeSave' => 'beforeSave',
		));
	}

	/**
	 * Used to respond to the beforeSave event.
	 * @param CEvent $event
	 */
	public function beforeSave($event) {
		if (false == $this->owner->canSetProperty($this->modificationAttribute)) {
			$this->owner->{$this->modificationAttribute} = new CDbExpression('NOW()');
		}
		return true;
	}
}
