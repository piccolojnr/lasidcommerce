# Stock and Inventory Guide

This guide explains the stock and inventory system in very simple terms.

## The Simple Idea
- `Inventory` means all the product units you physically have or plan to track.
- `Stock` means the count for one specific product or variant.
- A `stock item` is the record that stores those numbers.
- A `stock movement` is the history entry that explains why the number changed.

Think of it like this:
- `Stock item` = the current number on the shelf
- `Stock movement` = the note in the notebook explaining why the number changed

## The 3 Numbers That Matter

Every stock item has 3 important numbers:

### 1. Quantity on hand
This is how many units you physically have.

Example:
- You have 10 sneakers in the store room.
- `quantity_on_hand = 10`

### 2. Quantity reserved
This is how many units are already mentally spoken for.

These units are still physically there, but you should not promise them to someone else because they may already belong to an order or internal process.

Example:
- You have 10 sneakers physically.
- 3 are already reserved for customers.
- `quantity_reserved = 3`

### 3. Available quantity
This is what is really safe to sell right now.

Formula:

```text
available quantity = quantity on hand - quantity reserved
```

Example:
- On hand = 10
- Reserved = 3
- Available = 7

## What Reorder Level Means

`Reorder level` is the warning line.

It does not change stock by itself.
It only tells you:
"When stock falls to this number or lower, pay attention and restock soon."

Example:
- Reorder level = 5
- Available quantity = 4
- Result: this item is `low stock`

## The 3 Stock Statuses

### In stock
This means you still have enough available quantity.

Example:
- On hand = 20
- Reserved = 2
- Available = 18

### Low stock
This means you still have some quantity left, but it has dropped to the reorder warning level or lower.

Example:
- On hand = 8
- Reserved = 1
- Available = 7
- Reorder level = 7
- Result: `low stock`

### Out of stock
This means available quantity is 0 or below.

Example:
- On hand = 5
- Reserved = 5
- Available = 0
- Result: `out of stock`

## What “Track Inventory” Means

If `Track inventory` is turned on:
- the product is tied to stock records
- the admin should care about stock levels
- availability should come from real stock numbers

If `Track inventory` is turned off:
- the product is not being controlled by stock counts
- the system treats it more like “always available” from an inventory point of view

Simple version:
- Tracked product = “count it”
- Untracked product = “do not count it”

## What “Allow Backorders” Means

This decides what happens when stock runs out.

If `Allow backorders` is off:
- once stock is gone, customers should not keep buying it

If `Allow backorders` is on:
- customers can still place orders even when stock is exhausted

Simple version:
- Backorders off = “stop selling when empty”
- Backorders on = “keep selling even if empty”

## Why We Use Stock Movements

A stock number should not change like magic.

If stock goes from 12 to 9, someone should be able to answer:
- Who changed it?
- When was it changed?
- Why was it changed?
- Was it a restock, damage, return, or correction?

That is why stock movements exist.

They give a traceable history.

## Common Movement Types

### Restock
Use this when new units arrive.

Example:
- A supplier delivers 15 new bags.
- Movement type: `restock`
- Effect: stock goes up

### Return
Use this when sellable stock comes back and can be added again.

Example:
- A customer returns 1 unused wallet.
- Movement type: `return`
- Effect: stock goes up

### Correction add
Use this when the count was too low and you need to add missing units after checking reality.

Example:
- System says 4, but you count 6 on the shelf.
- Movement type: `correction_add`
- Effect: stock goes up

### Damage
Use this when units can no longer be sold.

Example:
- 2 pairs of shoes are damaged by water.
- Movement type: `damage`
- Effect: stock goes down

### Shrinkage
Use this when stock is missing for unclear operational reasons.

Example:
- You count 1 item missing and cannot explain it.
- Movement type: `shrinkage`
- Effect: stock goes down

### Correction remove
Use this when the system count was too high and you need to remove the extra amount after checking reality.

Example:
- System says 10, but you count only 8.
- Movement type: `correction_remove`
- Effect: stock goes down

## Why We Do Not Edit “Quantity on Hand” Directly

If people directly overwrite stock numbers, history becomes confusing.

Bad:
- Yesterday it was 20
- Today it is 12
- Nobody knows why

Better:
- Yesterday it was 20
- Today there is a `damage` movement for 8
- Now everyone understands why it became 12

That is why:
- `reorder level` can be edited directly
- actual stock count changes should go through a stock adjustment that also creates a stock movement

## A Very Simple Real Example

Let’s say you sell a sneaker.

Start:
- On hand = 12
- Reserved = 0
- Available = 12

Customer places an order for 2:
- On hand = 12
- Reserved = 2
- Available = 10

Warehouse confirms 1 pair was damaged:
- Create `damage` movement for 1
- On hand = 11
- Reserved = 2
- Available = 9

New delivery of 6 arrives:
- Create `restock` movement for 6
- On hand = 17
- Reserved = 2
- Available = 15

This is the main logic:
- `on hand` = physical reality
- `reserved` = already spoken for
- `available` = safe to sell
- `movements` = history of why things changed

## How To Think About The Admin Screens

### Stock Items page
Use this page to answer:
- What do we currently have?
- What is low?
- What is out of stock?
- Which product needs attention?

### Stock Item detail page
Use this page to:
- see one item clearly
- change reorder level
- record a manual stock adjustment
- review the movement history

### Stock Movements page
Use this page to answer:
- What changed recently?
- Was stock added or removed?
- Who made the change?
- What explanation was recorded?

## The Safest Everyday Rule

If you only remember one thing, remember this:

- If you want to change the warning threshold, edit `reorder level`.
- If you want to change the real stock count, record a `stock adjustment`.

That keeps the numbers clean and the history understandable.
