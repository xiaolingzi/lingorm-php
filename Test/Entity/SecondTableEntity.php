<?php
namespace LingORM\Test\Entity;

/**
 * @Table (name="second_table",database="")
 */
class SecondTableEntity
{
    /**
     * @Column (type="int",isGenerated=1,isPrimary=1)
     */
    public $id;
    
    /**
     * @Column (name="second_name", type="string")
     */
	public $secondName;
    
    /**
     * @Column (name="second_number", type="string")
     */
	public $secondNumber;
	
	/**
	 * @Column (name="second_time", type="datetime")
	 */
	public $secondTime;	
}