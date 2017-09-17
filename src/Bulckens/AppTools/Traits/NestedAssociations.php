<?php

namespace Bulckens\AppTools\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Bulckens\AppTools\Helpers\NestedAssociationsHelper;

trait NestedAssociations {

  // Association storage
  protected $associations = [];
  protected $associations_morgue = [];


  // Dynamic nested association mutator
  public function setNestedAssociationsAttribute( array $associations ) {
    if ( isset( $this->nested_associations ) ) {

      // check if nested association is allowed
      foreach ( $associations as $name => $items ) {

        if ( in_array( $name, $this->nested_associations ) ) {

          // typecast and store association(s)
          if ( NestedAssociationsHelper::hasOne( $this->$name() ) ) {
            if ( $instance = $this->findBuildOrKillNestedAssociation( $name, $items ) ) {
              $this->associations[$name] = $instance;
            }

          } elseif ( NestedAssociationsHelper::hasMany( $this->$name() ) ) {
            $this->associations[$name] = [];

            foreach ( $items as $item ) {
              if ( $instance = $this->findBuildOrKillNestedAssociation( $name, $item ) ) {
                array_push( $this->associations[$name], $instance );
              }
            }
          }
          
        } else {
          throw new NestedAssociationsNotAllowedException( "Nested association '$name' is not allowed" );
        }
      }

    } else {
      throw new NestedAssociationsUndefinedException( 'No nested association are defined' );
    }
  }


  // Nested association saver
  public function saveNestedAssociations() {
    if ( isset( $this->nested_associations ) && ! empty( $this->associations ) ) {

      foreach ( $this->associations as $name => $instances ) {
        
        // attach and save association(s)
        if ( NestedAssociationsHelper::hasOne( $this->$name() ) ) {
          $this->$name()->save( $instances );

        } elseif ( NestedAssociationsHelper::hasMany( $this->$name() ) ) {
          $this->$name()->saveMany( $instances );
        }

        // delete all instances at the morgue
        foreach ( $this->associations_morgue as $instance ) {
          $instance->delete();
        }

        // remove relation from eloquent relations cache
        $relations = $this->getRelations();
        unset( $relations[$name] );
        $this->setRelations( $relations );
      }
    }
  }
  

  // Find, build or delete instance for association
  protected function findBuildOrKillNestedAssociation( $name, $item ) {
    // get relation type
    $relation = $this->$name();

    // get class name from relation
    $class = get_class( $this->$name()->getModel() );

    // update an existing item
    if ( isset( $item['id'] ) ) {
      // make sure the item exists before updating or deleting
      if ( $instance = $relation->find( $item['id'] ) ) {
        return $this->updateOrDeleteNestedAssociation( $instance, $item );

      } else {
        throw new NestedAssociationRecordNotFoundException( "No $class could be found with id {$item['id']}" );  
      }

    // update existing one-to-one relation
    } elseif ( $relation instanceof HasOne && $this->$name ) {
      return $this->updateOrDeleteNestedAssociation( $this->$name, $item );

    // build a new relation
    } else {
      return new $class( $item );
    }
  }


  // Update or delete the instance
  protected function updateOrDeleteNestedAssociation( $instance, $item ) {
    if ( isset( $item['_delete'] ) && $item['_delete'] == 1 ) {
      array_push( $this->associations_morgue, $instance );
    } else {
      return $instance->fill( $item );
    }
  }

}


// Exceptions
class NestedAssociationsUndefinedException extends Exception {}
class NestedAssociationsNotAllowedException extends Exception {}
class NestedAssociationRecordNotFoundException extends Exception {}