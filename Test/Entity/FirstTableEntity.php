<?php
namespace LingORM\Test\Entity;

/**
 * @Table (name="first_table",database="test")
 */
class FirstTableEntity
{
    /**
     * @Column (type="int",isGenerated=1,isPrimary=1)
     */
    public $id;
    
    /**
     * @Column (name="first_name", type="string")
     */
	public $firstName;
    
    /**
     * @Column (name="first_number", type="string")
     */
	public $firstNumber;
	
	/**
	 * @Column (name="first_time", type="datetime")
	 */
	public $firstTime;	
}