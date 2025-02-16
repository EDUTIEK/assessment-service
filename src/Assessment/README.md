# Assessment Component

This component handles an assessment as a whole. 

## Api

The API provides two different factories of services:

* **ForClients**: Services that can be used by embedding client systems
* **ForRest**: Services handling REST calls by the web apps

## Authentication

This service authentifies REST calls based on tokens.

## Permissions

This service provides permission checks for users on assessments in a certain context.

## Supervision

This service provides functions to handle alerts and log entries for an assessment.


