import React, { useState } from 'react';
import { usePurchases, useCreatePurchase } from '../hooks/use-purchases';
import {
  Button,
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  Badge,
  Skeleton,
  EmptyState,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  Input,
  Label,
} from '@loyalty/ui';
import { Plus, ShoppingCart, Calendar, DollarSign } from 'lucide-react';
import { formatCurrency, formatDate } from '@loyalty/ui/lib/utils';
import type { PurchaseItem } from '@loyalty/types';

export const PurchasesPage: React.FC = () => {
  const [page, setPage] = useState(1);
  const { data, isLoading } = usePurchases(page);
  const { mutate: createPurchase, isPending } = useCreatePurchase();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [formData, setFormData] = useState({
    items: [{ name: '', quantity: 1, price: '' }],
  });

  const total = formData.items.reduce((sum, item) => {
    const price = parseFloat(item.price || '0');
    const qty = item.quantity || 0;
    return sum + qty * price;
  }, 0);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    const items: PurchaseItem[] = formData.items.map((item) => ({
      name: item.name!,
      quantity: item.quantity!,
      price: parseFloat(item.price!),
    }));

    const amount = items.reduce((sum, item) => sum + item.quantity * item.price, 0);

    createPurchase(
      {
        amount,
        items,
      },
      {
        onSuccess: () => {
          setDialogOpen(false);
          setFormData({
            items: [{ name: '', quantity: 1, price: '' }],
          });
        },
      }
    );
  };

  const addItem = () => {
    setFormData({
      ...formData,
      items: [...formData.items, { name: '', quantity: 1, price: '' }],
    });
  };

  const updateItem = (index: number, field: keyof PurchaseItem, value: any) => {
    const newItems = [...formData.items];
    newItems[index] = { ...newItems[index], [field]: value };
    setFormData({ ...formData, items: newItems });
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        {[...Array(3)].map((_, i) => (
          <Skeleton key={i} className="h-32" />
        ))}
      </div>
    );
  }

  const purchases = data?.data || [];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Purchase History</h1>
          <p className="text-muted-foreground mt-2">
            View your purchase history and create new purchases
          </p>
        </div>

        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogTrigger asChild>
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              New Purchase
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Create Purchase</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">

              <div className="space-y-3">
                <Label>Items</Label>
                {formData.items.map((item, index) => (
                  <div key={index} className="grid grid-cols-3 gap-2">
                    <Input
                      placeholder="Item name"
                      value={item.name}
                      onChange={(e) => updateItem(index, 'name', e.target.value)}
                      required
                    />
                    <Input
                      type="number"
                      placeholder="Qty"
                      value={item.quantity}
                      onChange={(e) => updateItem(index, 'quantity', parseInt(e.target.value))}
                      required
                    />
                    <Input
                      type="number"
                      step="0.01"
                      placeholder="Price"
                      value={item.price}
                      onChange={(e) => updateItem(index, 'price', e.target.value)}
                      required
                    />
                  </div>
                ))}
                <Button type="button" variant="outline" size="sm" onClick={addItem}>
                  Add Item
                </Button>
                <div className="flex justify-end">
                  <p className="text-sm font-medium">Total: {formatCurrency(total)}</p>
                </div>
              </div>

              <div className="flex gap-2">
                <Button type="submit" disabled={isPending}>
                  {isPending ? 'Creating...' : 'Create Purchase'}
                </Button>
                <Button type="button" variant="outline" onClick={() => setDialogOpen(false)}>
                  Cancel
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Purchases List */}
      {purchases.length > 0 ? (
        <div className="space-y-4">
          {purchases.map((purchase) => (
            <Card key={purchase.id}>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <CardTitle className="text-lg flex items-center gap-2">
                    <ShoppingCart className="h-5 w-5" />
                    Order #{purchase.order_id}
                  </CardTitle>
                  <Badge
                    variant={
                      purchase.status === 'completed'
                        ? 'default'
                        : purchase.status === 'pending'
                        ? 'secondary'
                        : 'destructive'
                    }
                  >
                    {purchase.status}
                  </Badge>
                </div>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm">
                    <DollarSign className="h-4 w-4 text-muted-foreground" />
                    <span className="font-semibold">{formatCurrency(purchase.amount)}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <Calendar className="h-4 w-4" />
                    <span>{formatDate(purchase.created_at, 'long')}</span>
                  </div>
                  <div className="mt-3">
                    <p className="text-sm font-medium mb-1">Items:</p>
                    <ul className="text-sm text-muted-foreground space-y-1">
                      {purchase.items.map((item, idx) => (
                        <li key={idx}>
                          {item.quantity}x {item.name} - {formatCurrency(item.price)}
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      ) : (
        <EmptyState
          icon={<ShoppingCart className="h-16 w-16" />}
          title="No purchases yet"
          description="Create your first purchase to start earning loyalty rewards!"
          action={{
            label: 'Create Purchase',
            onClick: () => setDialogOpen(true),
          }}
        />
      )}

      {/* Pagination */}
      {data && data.meta.last_page > 1 && (
        <div className="flex justify-center gap-2">
          <Button
            variant="outline"
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            disabled={page === 1}
          >
            Previous
          </Button>
          <span className="flex items-center px-4">
            Page {page} of {data.meta.last_page}
          </span>
          <Button
            variant="outline"
            onClick={() => setPage((p) => p + 1)}
            disabled={page === data.meta.last_page}
          >
            Next
          </Button>
        </div>
      )}
    </div>
  );
};