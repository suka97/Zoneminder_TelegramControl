# Zoneminder_TelegramControl

Adds telegram control functionality to a Zoneminder's camera such as:
* /ver: Sends the current image taken by the camera.
* /activar: Sets the current control mode as Mocord (motion detection).
* /desactivar: Sets the current control mode as Monitor (no motion detection). Can take an optional parameter as time to automatically restart the motion detection.
* /video: Sends the video corresponding to a Zoneminder event. The event can be specify either by typing it as an argument or by replying to an event alert.
* /alarma: Changes the state of a sound alarm controlled by the raspberry server.
* /agregar_user: Adds control permissions to a user, taking a second argument as user_id.
* /agregar_chat: Adds the current chat to the new event reporting list.
* /borrar_chat: Removes the current chat from the new event reporting list.
* /zip_all_events: Creates a zip with all events snapshots.
* /eventos_entre: 2022-10-12_01:00:00 2022-10-12_02:00:00

In addition to the available commands, the scripts also checks for new motion events and alerts the admin users by sending an image with the event_id and length as caption.

![Zoneminder_TelegramControl_screen1](https://user-images.githubusercontent.com/49660888/181652236-d8fdf4a3-ccef-4aec-8e24-9bc4f477c731.png)
