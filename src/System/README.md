# System Component

This component provides general services not bound to a single assessment.

## Api

The API provides two different factories of services:

* **ForClients**: Services that can be used by embedding client systems
* **ForServices**: Services that can be used in peer components of the assessment service

## Config

This service provides configuration and setup information.

A **Config** entity can be read and written by the client and is read by services. It has general configuration settings of the assesment service.

A **Setup** entity provides general settings of the client system which can't be changed.

## File

This service provides functions to store, read, delete and deliver files. Files are provided as a stream given by a PHP resource handles, together with an info giging name, size and mime type.

## ImageSketch

This service provides funcion to draw shapes (lines, waves, circles, rectangles and polygons) on an image. The source and reulting images are PHP file streams.

## PdfConverter

This service converts PDF files in one or more JPEG images of the pages. A source file is provides by a PHP resource handle. The results are **ImageDescriptor** giving resource handle, width, height and type.

## PdfCreator

This servides creates PDF files from HTML with **Options**.

## User

This service provides read-only data about a user account in the client system.

**UserData** has basic information: id, login name, title, name, language and time zone
**UserDisplay** has URLs to show an image or a profile page.