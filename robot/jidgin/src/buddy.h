/* $Id: buddy.h 11 2008-11-08 17:17:21Z whw $ */

#ifndef HAVA_BUDDY_H
#define HAVA_BUDDY_H

#ifdef __cplusplus
extern "C" {
#endif

#include <glib.h>

void *jidgin_buddy_get_handle(void);

void jidgin_buddy_cb_update(PurpleBuddy *);

GList *jidgin_buddy_lookup(const char *, const char *);

#endif  // HAVA_BUDDY_H

