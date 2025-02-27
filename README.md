# assessment-service

The assessment service helps a client system implementing assessments with long texts written by the participants. It consists of four components whch are connected by interfaces. 

* [System](src/System/README.md) provides general services not bound to a single assessment
* [Assessment](src/Assessment/README.md) provides services for an assessment as a whole
* [Task](src/Task/README.md) provides general services for an a sub task in an assessment
* [EssayTask](src/EssayTask/README.md) provides services for sub task type of writing an essay

## Implementation

Each component has separate directories for the services it provides. It has two additional directories: 

* **Api** contains *Factories* and a *Dependencies* interface.
* **Data** defines *Entities*, *Repositories* and *Data Transfer Objects*.

**Factories** create the services of the component. The API has different service factories for the client system or peer components. These sub factories are created by a single *Factory* class of the components which needs the *Dependencies* defined by the API.

**Dependencies** have to be provided by the infrastructure layer of the client system. These are mainly the *repositories* for the entities of the component. The *Dependencies* interface provides them by functions.
Its implementation by the client system should be lazy and cached, i.e. a dependency object should only be created when the function is called and it hould be reused when the function is called agsin.  

**Entities** represent persisted data. They are defined by abstract classes that must be extended in the infrastructure layer of the client system. They are read and written by the services. Their classes define abstract getter and setter function and may have additional functions based on these getters and setters. 

**Repositories** provide the CRUD functions to store and read entities. They are defined by interfaces that must be implemented in the infrastructure layer of the client system.

**Data Transfer Objects** are implemented as immutable objects with their properties in the constructor. They are read and written by the service functions but not in the repositories.
