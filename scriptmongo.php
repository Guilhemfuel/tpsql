<?php

mongorestore -d Base pxmongo/
db.px_users.find()

db.px_users.find(
    { type : "artisan" }
)

db.px_users.find(
    { "ville.values.zip" : /^33/ }
)

db.px_users.find(
    { "metiers" : {$exists:true, $not: {$size:0} }}
)

db.px_users.find().sort( { nom: 1, prenom: 1 } )

db.px_users.find().sort( { "ville.values.zip": 1 } )

db.px_users.count()

db.px_users.aggregate(
    { $group: { _id: '$type', count : {$sum : 1} } }
)