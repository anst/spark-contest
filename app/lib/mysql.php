<?php
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org)
*/

class MySQLConfig {
  public static $host = host;
  public static $db = db;
  public static $user = user;
  public static $pw = pw;   
}

class mysql
{
  private static $initialized = false;
  private static $keep_open   = true;
  private static $link;



  public static function queryp ( $query )
  {
    if ( $stmt = create_stmt( $query ) )
    {
      $stmt->store_result();

      $row = (object)null;
      $meta = $stmt->result_metadata();
      $params = array();

      while ( $field = $meta->fetch_field() )
      {
        $params[] = &$row->{$field->name};
      }

      call_user_func_array( array( $stmt, 'bind_result' ), $params );

      $result = array();
      while ( $stmt->fetch() )
      {
        $result[] = unserialize( serialize( $row ) );
      }

      self::$link->next_result();
      $stmt->close();
          
      return $result;
    }

    error_log( self::$link->error );
    return false;
  }



  public static function execp ( $query )
  {
    if ( $stmt = create_stmt( $query ) )
    {
      $affected = $stmt->affected_rows();
      $stmt->close();

      return $affected;
    }

    error_log( self::$link->error );
    return false;
  }



  public static function stat ()
  {
    if ( $stats = self::stat_string() )
    {
      $stats = explode( '	', $stats );
      $ret = array();

      foreach ( $stats as $stat )
      {
        $x = explode( ':', $stat );
        $ret[trim( $x[0] )] = trim( $x[1] );
      }

      return $ret;
    }

    return false;
  }



  private static function connect ( $keep_open = false )
  {
    if ( !self::$initialized )
    {
      self::$link = new mysqli(
        mysql_config::$host,
        mysql_config::$user,
        mysql_config::$pw,
        mysql_config::$db );

      if ( !self::$link->connect_error )
      {
        if ( !self::$keep_open )
        {
          register_shutdown_function( 'mysqli_close', self::$link );
        }

        self::$initialized = true;
      }
    }

    return self::$initialized;
  }



  private static function create_stmt ( $query )
  {
    if ( self::connect() )
    {
      $args = func_get_args();
      array_shift( $args );

      if ( $stmt = self::$link->prepare( self::escape( $query ) ) )
      {
        $refs = array( array_shift( $args ) );
        foreach ( $args as &$arg )
        {
          $refs[] = &$arg;
        }

        if ( count( $refs ) > 1 ) {
          call_user_func_array( array( $stmt, 'bind_param' ), $refs );
        }

        $stmt->execute();

        return $stmt;
      }
    }
    return false;
  }



  private static function escape ( $value )
  {
    if ( self::connect() )
      return mysqli_real_escape_string( self::$link, $value );
    else
      return mysql_escape_string( $value );
  }



  private static function stat_string ()
  {
    return self::connect() ? self::$link->stat() : false;
  }
}