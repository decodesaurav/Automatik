import { InlineStack, RadioButton, Select, TextField,Text, BlockStack } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function CollectionSelect() {
  const { t } = useTranslation();
  const [method, setMethod] = useState('increase');
  const [value, setValue] = useState(0);

  return (
    <>
        <BlockStack gap={100}>
            <Text>Where product is in Collection</Text>
            <InlineStack gap={200}>
                    <Select
                        options={[
                            {
                                label: "Ladies Clothing",
                                value: "greater_than",
                            },
                            {
                                label: "Kids Footwear",
                                value: "less_than",
                            },
                        ]}
                        // onChange={handleAdjustmentChange}
                        value={method}
                    />
                </InlineStack>
        </BlockStack>
    </>
  );

}
